<?php

use App\Models\Product;
use Illuminate\Database\QueryException;
use Inertia\Testing\AssertableInertia as Assert;

/*
|--------------------------------------------------------------------------
| Public product page (/pricing/{slug})
|--------------------------------------------------------------------------
| The products table already shipped with a unique `slug` column, so there
| is no migration/hook to test here. These tests cover what actually exists:
| the factory-provided slug, the database uniqueness guarantee, and the
| active-only resolution of the public product page.
*/

it('persists a slug on products created through the factory', function () {
    $product = Product::factory()->create();

    expect($product->slug)->toBeString()->not->toBe('');
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'slug' => $product->slug,
    ]);
});

it('rejects a duplicate product slug at the database level', function () {
    Product::factory()->create(['slug' => 'duplicate-slug']);

    expect(fn () => Product::factory()->create(['slug' => 'duplicate-slug']))
        ->toThrow(QueryException::class);
});

it('renders the product page for an active product', function () {
    $product = Product::factory()->create([
        'name' => 'Corevisys Pro',
        'slug' => 'corevisys-pro',
        'is_active' => true,
    ]);

    $product->prices()->create([
        'currency' => 'USD',
        'amount' => 49.00,
        'type' => 'full',
        'billing_period' => null,
    ]);

    $product->prices()->create([
        'currency' => 'BDT',
        'amount' => 5900.00,
        'type' => 'full',
        'billing_period' => null,
    ]);

    $response = $this->get(route('pricing.product', ['slug' => 'corevisys-pro']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Public/Product')
        ->where('product.slug', 'corevisys-pro')
        ->where('product.name', 'Corevisys Pro')
        ->has('product.prices', 2)
    );
});

it('returns a 404 for an unknown product slug', function () {
    $response = $this->get(route('pricing.product', ['slug' => 'does-not-exist']));

    $response->assertStatus(404);
    $response->assertInertia(fn (Assert $page) => $page->component('Public/NotFound'));
});

it('returns a 404 for an inactive product slug', function () {
    Product::factory()->create([
        'slug' => 'retired-product',
        'is_active' => false,
    ]);

    $response = $this->get(route('pricing.product', ['slug' => 'retired-product']));

    $response->assertStatus(404);
    $response->assertInertia(fn (Assert $page) => $page->component('Public/NotFound'));
});