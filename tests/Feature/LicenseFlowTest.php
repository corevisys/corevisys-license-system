<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

        // 2. Buy (Order)
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/orders/create', [
            'product_id' => $product->id,
            'gateway' => 'test'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('order.status', 'completed');

        $licenseKey = $response->json('new_license_key');
        $this->assertNotNull($licenseKey);

        // 3. Activate (First Time - Binding)
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

        // 4. Verify Binding (Same Domain)
        $checkResponse = $this->postJson('/api/v1/license/check', [
            'license_key' => $licenseKey,
            'domain' => $domain,
            'ip' => '1.2.3.4' // IP might change, but Domain is strict in our Logic? 
            // Wait, my service logic updates bound_ip on first use, but validates logic:
            // "If bound_domain matches, proceed."
            // My Service code: if (bound_domain !== domain) -> error.
            // It doesn't strictly check IP for failure, only logs it?
            // "If Valid: Return Status active".
        ]);

        $checkResponse->assertStatus(200);

        // 5. Verify Failure (Different Domain)
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
