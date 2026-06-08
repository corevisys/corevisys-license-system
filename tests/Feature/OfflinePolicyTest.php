<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflinePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_activate_returns_offline_validation_fields_and_headers()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $service = new \App\Services\LicenseService();
        $license = $service->createLicense($order, $product);
        $rawKey = $license->raw_key;

        $response = $this->postJson('/api/v1/license/activate', [
            'license_key' => $rawKey,
            'domain' => 'offline.com',
            'ip' => '127.0.0.1',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'license_status',
                    'signature',
                    'offline_valid_until' // New Field
                ]
            ]);

        // Check Headers (Laravel default ordering seems to be max-age first)
        $response->assertHeader('Cache-Control', 'max-age=3600, private');
    }
}
