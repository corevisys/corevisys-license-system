<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GracePeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_is_active_during_grace_period()
    {
        // 1. Setup Expired License but inside Grace Period
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'GRACE-1',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'GRACEKEY'),
            'type' => 'subscription',
            'status' => 'active',
            'expires_at' => Carbon::now()->subDay(), // Expired yesterday
            'grace_expires_at' => Carbon::now()->addDays(5), // Grace covers it
        ]);

        // 2. Check Validity
        $service = new LicenseService();
        $result = $service->activate('GRACEKEY', 'example.com', '127.0.0.1');

        // 3. Verify
        $this->assertTrue($result['status']);
        $this->assertEquals('active', $result['license']->status);
    }

    public function test_license_expires_after_grace_period()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'GRACE-2',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'EXPIREDKEY'),
            'type' => 'subscription',
            'status' => 'active',
            'expires_at' => Carbon::now()->subDays(10), // Expired long ago
            'grace_expires_at' => Carbon::now()->subDay(), // Grace also over
        ]);

        $service = new LicenseService();
        $result = $service->activate('EXPIREDKEY', 'example.com', '127.0.0.1');

        $this->assertFalse($result['status']);
        $this->assertEquals('License Expired', $result['message']);

        $license->refresh();
        $this->assertEquals('expired', $license->status);
    }
}
