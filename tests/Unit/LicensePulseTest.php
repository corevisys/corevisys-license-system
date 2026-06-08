<?php

test('example', function () {
    expect(true)->toBeTrue();
});

test('pulse endpoint validates active license', function () {
    $user = \App\Models\User::factory()->create();
    $product = \App\Models\Product::factory()->create();
    $order = \App\Models\Order::factory()->create(['user_id' => $user->id]);

    $license = \App\Models\License::create([
        'id' => \Illuminate\Support\Str::uuid(),
        'user_id' => $user->id,
        'product_id' => $product->id,
        'order_id' => $order->id,
        'license_key_hash' => hash('sha256', 'VALID-KEY-PULSE'),
        'status' => 'active',
        'bound_domain' => 'test.com',
        'expires_at' => now()->addMonth(),
    ]);

    $response = $this->postJson('/api/v1/license/pulse', [
        'license_key' => 'VALID-KEY-PULSE',
        'domain' => 'test.com'
    ], ['X-API-Version' => '1.0']);

    $response->assertStatus(200)
        ->assertJsonPath('status', 'success');
});

test('pulse endpoint rejects expired license no grace', function () {
    $license = \App\Models\License::factory()->create([
        'license_key_hash' => hash('sha256', 'EXPIRED-KEY'),
        'status' => 'active',
        'bound_domain' => 'expired.com',
        'expires_at' => now()->subDay(),
        'grace_expires_at' => null
    ]);

    $response = $this->postJson('/api/v1/license/pulse', [
        'license_key' => 'EXPIRED-KEY',
        'domain' => 'expired.com'
    ], ['X-API-Version' => '1.0']);

    $response->assertStatus(403)
        ->assertJsonPath('message', 'License Expired');
});
