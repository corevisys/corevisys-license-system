<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_analytics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $product = Product::factory()->create(['name' => 'Elite Software']);

        // Create some licenses
        License::factory()->count(3)->create(['product_id' => $product->id, 'status' => 'active', 'type' => 'full']);
        License::factory()->count(2)->create(['product_id' => $product->id, 'status' => 'expired', 'type' => 'trial']);

        // Create some verified revenue
        $order = Order::factory()->create(['user_id' => $user->id]);
        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'verified',
            'amount' => 100,
            'base_currency_amount' => 100
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/analytics');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ])
            ->assertJsonFragment(['status' => 'active', 'count' => 3])
            ->assertJsonFragment(['type' => 'full', 'count' => 3])
            ->assertJsonFragment(['total_revenue_usd' => 100]);
    }

    public function test_regular_user_cannot_view_analytics()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->getJson('/api/v1/admin/analytics');
        $response->assertStatus(403);
    }
}
