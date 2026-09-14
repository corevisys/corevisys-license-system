<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupExpiredLicensesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_expired_licenses_removes_old_records_and_related_activations(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::create([
            'order_number' => 'CLN-1',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'CLNKEY' . 'salt-clnkey'),
            'secret_salt' => 'salt-clnkey',
            'type' => 'full',
            'status' => 'expired',
            'expires_at' => Carbon::now()->subDays(120),
        ]);

        LicenseActivation::create([
            'license_id' => $license->id,
            'request_ip' => '127.0.0.1',
            'request_domain' => 'example.com',
            'status' => 'success',
        ]);

        $this->artisan('license:cleanup-expired', ['--days' => 90])
            ->assertSuccessful();

        $this->assertDatabaseMissing('licenses', ['id' => $license->id]);
        $this->assertDatabaseMissing('license_activations', ['license_id' => $license->id]);
    }
}
