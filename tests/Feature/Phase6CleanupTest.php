<?php

namespace Tests\Feature;

use App\Jobs\ProcessLicenseRenewal;
use App\Jobs\SendExpiryNotification;
use App\Models\License;
use App\Models\Order;
use App\Models\TrialHistory;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\ReceiptStorageService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase6CleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_trial_history_records_user_and_license_linkage(): void
    {
        $user = User::factory()->create(['email' => 'trial@example.com']);
        $product = \App\Models\Product::factory()->create();
        $order = Order::create([
            'order_number' => 'TRIAL-LINK-1',
            'user_id' => $user->id,
            'total_amount' => 0,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $service = new LicenseService();
        $license = $service->createLicense($order, $product, 'trial');

        $history = TrialHistory::query()->latest()->first();

        $this->assertNotNull($history);
        $this->assertSame($user->id, $history->user_id);
        $this->assertSame($license->id, $history->license_id);
    }

    public function test_scheduler_runs_due_commands_for_renew_notify_and_cleanup(): void
    {
        $user = User::factory()->create();
        $product = \App\Models\Product::factory()->create();

        Queue::fake();
        Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));

        $renewOrder = Order::create([
            'order_number' => 'RENEW-SCHED-1',
            'user_id' => $user->id,
            'total_amount' => 0,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $renewOrder->id,
            'license_key_hash' => hash('sha256', 'RENEW-SCHED' . 'salt-renew-schedule'),
            'secret_salt' => 'salt-renew-schedule',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'next_billing_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
        ]);

        $this->artisan('license:renew-subscriptions')->assertExitCode(0);
        Queue::assertPushed(ProcessLicenseRenewal::class, 1);

        Queue::fake();
        Carbon::setTestNow(Carbon::create(2026, 1, 1, 1, 0, 0));

        $notifyOrder = Order::create([
            'order_number' => 'NOTIFY-SCHED-1',
            'user_id' => $user->id,
            'total_amount' => 0,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $notifyOrder->id,
            'license_key_hash' => hash('sha256', 'NOTIFY-SCHED' . 'salt-notify-schedule'),
            'secret_salt' => 'salt-notify-schedule',
            'type' => 'subscription',
            'status' => 'active',
            'expires_at' => now()->addDays(7),
        ]);

        $this->artisan('license:notify-expiring')->assertExitCode(0);
        Queue::assertPushed(SendExpiryNotification::class, 1);

        Carbon::setTestNow(Carbon::create(2026, 1, 1, 0, 0, 0));
        $cleanupOrder = Order::create([
            'order_number' => 'CLEANUP-TRIAL-1',
            'user_id' => $user->id,
            'total_amount' => 0,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $expiredLicense = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $cleanupOrder->id,
            'license_key_hash' => hash('sha256', 'CLEANUP-LIC' . 'salt-cleanup-lic'),
            'secret_salt' => 'salt-cleanup-lic',
            'type' => 'trial',
            'status' => 'expired',
            'expires_at' => now()->subDays(200),
        ]);

        $this->assertDatabaseHas('licenses', ['id' => $expiredLicense->id]);
        $this->artisan('license:cleanup-expired', ['--days' => 90])->assertExitCode(0);
        $this->assertDatabaseMissing('licenses', ['id' => $expiredLicense->id]);

        Carbon::setTestNow();
    }

    public function test_s3_receipt_storage_upload_and_retrieve_and_scan_validation(): void
    {
        Storage::fake('s3');
        config()->set('receipt.storage_disk', 's3');
        config()->set('receipt.scan_endpoint', 'https://scanner.example.test/scan');
        config()->set('receipt.scan_api_key', 'scan-key');

        Http::fake([
            'https://scanner.example.test/scan' => Http::response(['clean' => true], 200),
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('receipt.pdf', 10, 'application/pdf');

        $path = app(ReceiptStorageService::class)->storeUploadedReceipt($file);

        $this->assertNotEmpty($path);
        $this->assertTrue(Storage::disk('s3')->exists($path));
        $this->assertSame(file_get_contents($file->getPathname()), Storage::disk('s3')->get($path));
        Http::assertSent(fn ($request) => str_contains($request->url(), '/scan'));
    }
}
