<?php

test('renewal logic uses yearly billing period correctly', function () {
    $product = \App\Models\Product::factory()->create();
    $price = \App\Models\ProductPrice::create([
        'product_id' => $product->id,
        'currency' => 'USD',
        'amount' => 100,
        'type' => 'full',
        'billing_period' => 'yearly'
    ]);

    $license = \App\Models\License::factory()->create([
        'product_id' => $product->id,
        'auto_renew' => true,
        'status' => 'active',
        'next_billing_at' => now()->subDay(),
        'expires_at' => now()->subDay(),
    ]);

    $service = new \App\Services\LicenseService(new \App\Services\LicenseStateMachine());
    $results = $service->processRenewals();

    expect($results['success'])->toBe(1);
    $license->refresh();

    // Should be extended by 1 year
    expect($license->expires_at->year)->toBe(now()->addYear()->year);
});
