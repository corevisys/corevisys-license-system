<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountedRenewalOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_renewal_order_ignores_tampered_total_amount_in_request_input(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 100.00,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $initialOrder = Order::create([
            'order_number' => 'ORD-INIT-TAMPER-1',
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $initialOrder->id,
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'price' => $price->amount,
            'license_type' => 'subscription',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $initialOrder->id,
            'license_key_hash' => hash('sha256', 'TAMPER-TEST-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->addDays(5),
            'next_billing_at' => Carbon::now()->addDays(5),
        ]);

        // Attacker submits request with tampered total_amount = 5.00
        $response = $this->actingAs($user)->post("/licenses/{$license->id}/renew", [
            'total_amount' => 5.00,
            'gateway' => 'stripe',
        ]);

        $renewalOrder = Order::where('type', 'renewal')->where('license_id', $license->id)->first();
        $this->assertNotNull($renewalOrder);
        // Server MUST have computed 100.00, completely ignoring the submitted 5.00
        $this->assertEquals(100.00, (float) $renewalOrder->total_amount);
    }

    public function test_renewal_order_with_valid_discount_coupon_computes_discounted_total_server_side(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 100.00,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $initialOrder = Order::create([
            'order_number' => 'ORD-INIT-COUPON-1',
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $initialOrder->id,
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'price' => $price->amount,
            'license_type' => 'subscription',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $initialOrder->id,
            'license_key_hash' => hash('sha256', 'COUPON-TEST-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->addDays(5),
            'next_billing_at' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($user)->post("/licenses/{$license->id}/renew", [
            'coupon' => 'SAVE20',
            'gateway' => 'stripe',
        ]);

        $renewalOrder = Order::where('type', 'renewal')->where('license_id', $license->id)->first();
        $this->assertNotNull($renewalOrder);
        // 20% off 100 = 80.00 computed server side
        $this->assertEquals(80.00, (float) $renewalOrder->total_amount);
    }

    public function test_renewal_order_with_total_zero_coupon_is_rejected_without_admin_approval(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 100.00,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $initialOrder = Order::create([
            'order_number' => 'ORD-INIT-FREE-1',
            'user_id' => $user->id,
            'total_amount' => 100.00,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $initialOrder->id,
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'price' => $price->amount,
            'license_type' => 'subscription',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $initialOrder->id,
            'license_key_hash' => hash('sha256', 'FREE-TEST-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->addDays(5),
            'next_billing_at' => Carbon::now()->addDays(5),
        ]);

        // Request 100% coupon (FREE100)
        $response = $this->actingAs($user)->post("/licenses/{$license->id}/renew", [
            'coupon' => 'FREE100',
            'gateway' => 'stripe',
        ]);

        $response->assertSessionHas('error');
        // No renewal order created for total 0 without explicit admin approval
        $this->assertNull(Order::where('type', 'renewal')->where('license_id', $license->id)->first());
    }

    public function test_license_service_rejects_automatic_renewal_for_total_zero_renewal_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $initialOrder = Order::create([
            'order_number' => 'ORD-INIT-ZERO-QUAL',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $initialOrder->id,
            'license_key_hash' => hash('sha256', 'ZERO-ORDER-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        // A renewal order exists with total_amount = 0 (e.g. forced or 100% discount)
        $zeroOrder = Order::create([
            'order_number' => 'ORD-ZERO-REN',
            'user_id' => $user->id,
            'license_id' => $license->id,
            'total_amount' => 0,
            'currency' => 'USD',
            'status' => 'completed',
            'type' => 'renewal',
        ]);

        $zeroPayment = Payment::create([
            'order_id' => $zeroOrder->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'manual',
            'transaction_id' => 'tx_zero_pay',
            'amount' => 0,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();
        // Automatic renewal must NOT be granted for total 0
        $this->assertFalse($service->renewLicense($license));
        $this->assertNull($zeroPayment->fresh()->applied_at);
    }
}
