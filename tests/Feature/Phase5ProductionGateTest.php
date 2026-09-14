<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5ProductionGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_activation_endpoint_rate_limits_requests(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::create([
            'order_number' => 'RATE-ACT-1',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'RATE-ACT-KEY' . 'salt-rate-act-key'),
            'secret_salt' => 'salt-rate-act-key',
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'rate-act.test',
            'bound_ip' => '127.0.0.1',
            'bound_fingerprint' => 'fp-rate-act',
            'expires_at' => now()->addMonth(),
            'activated_at' => now(),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/license/activate', [
                'license_key' => 'RATE-ACT-KEY',
                'domain' => 'rate-act.test',
                'ip' => '127.0.0.1',
                'fingerprint' => 'fp-rate-act',
            ]);
        }

        $response = $this->postJson('/api/v1/license/activate', [
            'license_key' => 'RATE-ACT-KEY',
            'domain' => 'rate-act.test',
            'ip' => '127.0.0.1',
            'fingerprint' => 'fp-rate-act',
        ]);

        $response->assertStatus(429)
            ->assertJsonStructure(['message']);
    }

    public function test_pulse_endpoint_rate_limits_requests(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::create([
            'order_number' => 'RATE-PULSE-1',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'RATE-PULSE-KEY' . 'salt-rate-pulse-key'),
            'secret_salt' => 'salt-rate-pulse-key',
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'rate-pulse.test',
            'bound_ip' => '127.0.0.1',
            'bound_fingerprint' => 'fp-rate-pulse',
            'expires_at' => now()->addMonth(),
            'activated_at' => now(),
        ]);

        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/v1/license/pulse', [
                'license_key' => 'RATE-PULSE-KEY',
                'domain' => 'rate-pulse.test',
                'fingerprint' => 'fp-rate-pulse',
            ]);
        }

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => 'RATE-PULSE-KEY',
            'domain' => 'rate-pulse.test',
            'fingerprint' => 'fp-rate-pulse',
        ]);

        $response->assertStatus(429)
            ->assertJsonStructure(['message']);
    }
}
