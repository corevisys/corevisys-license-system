<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSlugGuardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_cannot_change_slug_when_licenses_exist_via_explicit_slug(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Product',
            'slug' => 'original-product',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        License::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);

        $payload = [
            'id' => $product->id,
            'name' => 'Original Product',
            'slug' => 'changed-product-slug',
            'description' => 'Updated description',
            'is_active' => true,
            'prices' => [
                ['currency' => 'USD', 'amount' => 49, 'type' => 'full'],
            ],
        ];

        $response = $this->actingAs($this->admin)->postJson(route('admin.products.save'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);

        $product->refresh();
        $this->assertSame('original-product', $product->slug);
    }

    public function test_admin_cannot_change_name_resulting_in_new_slug_when_licenses_exist(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Product',
            'slug' => 'original-product',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        License::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);

        $payload = [
            'id' => $product->id,
            'name' => 'Renamed Product That Changes Slug',
            'description' => 'Updated description',
            'is_active' => true,
            'prices' => [
                ['currency' => 'USD', 'amount' => 49, 'type' => 'full'],
            ],
        ];

        $response = $this->actingAs($this->admin)->postJson(route('admin.products.save'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);

        $product->refresh();
        $this->assertSame('original-product', $product->slug);
    }

    public function test_admin_can_update_product_details_when_licenses_exist_if_slug_is_preserved(): void
    {
        $product = Product::factory()->create([
            'name' => 'Enterprise Suite',
            'slug' => 'enterprise-suite',
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        License::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);

        $payload = [
            'id' => $product->id,
            'name' => 'Enterprise Suite',
            'slug' => 'enterprise-suite',
            'description' => 'Brand new description for enterprise suite',
            'is_active' => false,
            'prices' => [
                ['currency' => 'USD', 'amount' => 99, 'type' => 'subscription', 'billing_period' => 30],
            ],
        ];

        $response = $this->actingAs($this->admin)->postJson(route('admin.products.save'), $payload);

        $response->assertRedirect()->assertSessionHas('success');

        $product->refresh();
        $this->assertSame('enterprise-suite', $product->slug);
        $this->assertSame('Brand new description for enterprise suite', $product->description);
        $this->assertFalse($product->is_active);
    }

    public function test_admin_can_change_slug_when_no_licenses_exist(): void
    {
        $product = Product::factory()->create([
            'name' => 'Draft Product',
            'slug' => 'draft-product',
            'is_active' => true,
        ]);

        $payload = [
            'id' => $product->id,
            'name' => 'Brand New Name',
            'slug' => 'brand-new-slug',
            'description' => 'No licenses exist, so rename is safe',
            'is_active' => true,
            'prices' => [
                ['currency' => 'USD', 'amount' => 19, 'type' => 'full'],
            ],
        ];

        $response = $this->actingAs($this->admin)->postJson(route('admin.products.save'), $payload);

        $response->assertRedirect()->assertSessionHas('success');

        $product->refresh();
        $this->assertSame('brand-new-slug', $product->slug);
        $this->assertSame('Brand New Name', $product->name);
    }

    public function test_model_level_guard_throws_domain_exception_when_slug_dirtied_with_licenses(): void
    {
        $product = Product::factory()->create([
            'name' => 'Core App',
            'slug' => 'core-app',
        ]);

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        License::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('The product slug cannot be changed while licenses exist for this product.');

        $product->slug = 'new-core-app';
        $product->save();
    }

    public function test_model_level_guard_allows_slug_change_when_no_licenses(): void
    {
        $product = Product::factory()->create([
            'name' => 'Core App',
            'slug' => 'core-app',
        ]);

        $product->slug = 'new-core-app';
        $product->save();

        $this->assertSame('new-core-app', $product->fresh()->slug);
    }
}
