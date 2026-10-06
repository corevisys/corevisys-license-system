<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class TrialAbuseTest extends TestCase
{
    use RefreshDatabase;

    public function test_prevents_duplicate_trial_via_email()
    {
        $user = User::factory()->create(['email' => 'abuser@example.com']);
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'TRIAL-1',
            'user_id' => $user->id,
            'total_amount' => 0,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        $service = new LicenseService();
        // 1. First Trial -> Success
        $service->createLicense($order, $product, 'trial');

        // 2. Second Trial -> Fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Trial limit exceeded');

        $service->createLicense($order, $product, 'trial');
    }

    public function test_prevents_duplicate_trial_via_fingerprint()
    {
        // User 1
        $user1 = User::factory()->create();
        $product = Product::factory()->create();
        $order1 = \App\Models\Order::create([
            'order_number' => 'TRIAL-USER-1',
            'user_id' => $user1->id,
            'total_amount' => 0,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        // Setup separate service instances to simulate requests? 
        // No, just same logic.

        $service = new LicenseService();
        $license1 = $service->createLicense($order1, $product, 'trial');

        // Activate (Records Fingerprint)
        $fingerprint = 'unique_device_hash_123';
        $result1 = $service->activate($license1->raw_key, 'domain1.com', '1.2.3.4', $fingerprint);

        $this->assertTrue($result1['status']);

        // User 2 (Different Email, Same Machine/Fingerprint)
        $user2 = User::factory()->create(['email' => 'other@example.com']);
        $order2 = \App\Models\Order::create([
            'order_number' => 'TRIAL-USER-2',
            'user_id' => $user2->id,
            'total_amount' => 0,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        // Create succeeds (different email/IP check skipped for simplicity in test env or assumes diff IP)
        // Note: createLicense checks IP hash too. In test, request()->ip() is 127.0.0.1 always?
        // If createLicense blocks on IP, this test might fail earlier.
        // We should allow createLicense to pass (maybe clear DB trial history for IP?) or mock IP.
        // Let's manually clear the TrialHistory created by license1 for IP/Email, but KEEP fingerprint.

        \App\Models\TrialHistory::whereNotNull('email_hash')->delete();
        // Only keep fingerprint records (which are created at activation)
        // Wait, createLicense CREATES the record.
        // And activate CREATES a record.
        // So we will have 2 records.

        $license2 = $service->createLicense($order2, $product, 'trial');

        // Activate with SAME fingerprint -> should fail
        $result2 = $service->activate($license2->raw_key, 'domain2.com', '5.6.7.8', $fingerprint);

        $this->assertFalse($result2['status']);
        $this->assertStringContainsString('Trial already used', $result2['message']);
    }
}
