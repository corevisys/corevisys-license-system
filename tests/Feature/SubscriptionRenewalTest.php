<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionRenewalTest extends TestCase
{
    use RefreshDatabase;

    public function test_renewal_command_extends_expiry()
    {
        // 1. Setup
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'SUB-TEST',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'SUBKEY'),
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(), // Expired
            'next_billing_at' => Carbon::now()->subMinute(), // Due
            'gateway_subscription_id' => 'sub_123'
        ]);

        // 2. Run Service directly (or command)
        $service = new LicenseService();
        $results = $service->processRenewals();

        // 3. Verify
        $this->assertEquals(1, $results['success']);

        $license->refresh();
        $this->assertTrue($license->expires_at->isFuture());
        $this->assertTrue($license->next_billing_at->isFuture());
    }
}
