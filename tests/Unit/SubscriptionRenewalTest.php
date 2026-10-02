<?php

use App\Models\License;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Services\LicenseService;
use Carbon\Carbon;

test('renewal logic uses billing period in days', function () {
    $product = Product::factory()->create();
    $price = ProductPrice::create([
        'product_id' => $product->id,
        'currency' => 'USD',
        'amount' => 100,
        'type' => 'full',
        'billing_period' => 365,
    ]);

    // License was created 30 days ago; it expired yesterday
    $license = License::factory()->create([
        'product_id'     => $product->id,
        'auto_renew'     => true,
        'status'         => 'active',
        'created_at'     => Carbon::now()->subDays(30),
        'next_billing_at' => Carbon::now()->subDay(),
        'expires_at'     => Carbon::now()->subDay(),
    ]);

    // Renewal-cycle payment recorded now (strictly after license creation)
    Payment::create([
        'order_id'       => $license->order_id,
        'user_id'        => $license->user_id,
        'gateway'        => 'manual',
        'transaction_id' => 'tx_unit_test',
        'amount'         => 100,
        'status'         => 'verified',
        'created_at'     => Carbon::now(),
    ]);

    $service = new LicenseService();
    $results = $service->processRenewals();

    expect($results['success'])->toBe(1);

    $license->refresh();

    // Should be extended by 365 days from the previous expiry.
    $expected = now()->subDay()->addDays(365)->startOfDay();
    expect($license->expires_at->startOfDay()->format('Y-m-d'))->toBe($expected->format('Y-m-d'));
    expect($license->next_billing_at->isAfter($license->expires_at))->toBeTrue();
});
