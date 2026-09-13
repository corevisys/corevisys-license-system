<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class LicenseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_buy_and_activate_license()
    {
        // 1. Setup Data
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Super SaaS', 'is_active' => true]);
        $product->prices()->create([
            'currency' => 'USD',
            'amount' => 99.00,
            'type' => 'full'
        ]);

        // 2. Buy (Order) - creates a pending order awaiting payment.
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/orders/create', [
            'product_id' => $product->id,
            'gateway' => 'test'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('order.status', 'pending');

        $order = \App\Models\Order::findOrFail($response->json('order.id'));

        // 3. Fulfillment happens only after payment is confirmed
        //    (Stripe webhook / admin approval / OrderFulfillmentService).
        $fulfillment = app(\App\Services\OrderFulfillmentService::class)->fulfillOrder($order, []);
        $licenseKey = $fulfillment['license']->raw_key ?? $fulfillment['license']->license_key;
        $this->assertNotNull($licenseKey);

        // 4. Activate (First Time - Binding)
        $domain = 'example.com';
        $ip = '127.0.0.1';

        $activateResponse = $this->postJson('/api/v1/license/activate', [
            'license_key' => $licenseKey,
            'domain' => $domain,
            'ip' => $ip
        ]);

        $activateResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.license_status', 'active');

        // 5. Verify Binding (Same Domain) - read-only check has no side effects.
        $checkResponse = $this->postJson('/api/v1/license/check', [
            'license_key' => $licenseKey,
            'domain' => $domain,
            'ip' => '1.2.3.4'
        ]);

        $checkResponse->assertStatus(200);

        // 6. Verify Failure (Different Domain)
        $failResponse = $this->postJson('/api/v1/license/activate', [
            'license_key' => $licenseKey,
            'domain' => 'thief.com', // Mismatch
            'ip' => $ip
        ]);

        $failResponse->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'Invalid Domain. Bound to: ' . $domain);
    }
}
