<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LicenseResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reset_license_domain()
    {
        // 1. Setup
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = \App\Models\Order::create([
            'order_number' => 'TEST-123',
            'user_id' => $user->id,
            'total_amount' => 99,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'TESTKEY'),
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'old.com',
            'bound_ip' => '1.1.1.1',
            'activated_at' => now(),
        ]);

        // 2. Act as Admin
        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/v1/admin/licenses/{$license->id}/reset", [
            'reason' => 'Moving to new server'
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 3. Verify DB
        $this->assertNull($license->refresh()->bound_domain);
        $this->assertNull($license->bound_ip);
        $this->assertEquals(1, $license->reset_count);

        $this->assertDatabaseHas('license_resets', [
            'license_id' => $license->id,
            'admin_id' => $admin->id,
            'reason' => 'Moving to new server',
            'previous_domain' => 'old.com'
        ]);
    }

    public function test_regular_user_cannot_reset_license()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'TEST-456',
            'user_id' => $user->id,
            'total_amount' => 99,
            'currency' => 'USD',
            'status' => 'completed'
        ]);
        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'TESTKEY'),
            'type' => 'full',
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/admin/licenses/{$license->id}/reset", [
            'reason' => 'Hacker'
        ]);

        $response->assertStatus(403);
    }
}
