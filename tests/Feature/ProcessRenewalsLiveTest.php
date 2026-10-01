<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\License;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * FIX-005 / BUG-001: Verification that LicenseService::processRenewals()
 * enforces real payment confirmation and never grants free license renewals
 * via simulated payment stubs ($paymentSuccess = true removed).
 */
class ProcessRenewalsLiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_renewal_fails_and_starts_grace_when_no_payment_record_exists(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        ProductPrice::create([
            'product_id'     => $product->id,
            'currency'       => 'USD',
            'amount'         => 50,
            'type'           => 'subscription',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-UNPAID-1',
            'user_id'      => $user->id,
            'total_amount' => 50,
            'currency'     => 'USD',
            'status'       => 'pending',
        ]);

        $pastExpiry = Carbon::now()->subMinute();
        $license = License::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'order_id'         => $order->id,
            'license_key_hash' => hash('sha256', 'UNPAID-KEY-salt'),
            'secret_salt'      => 'salt',
            'type'             => 'subscription',
            'status'           => 'active',
            'auto_renew'       => true,
            'expires_at'       => $pastExpiry,
            'next_billing_at'  => $pastExpiry,
        ]);

        $service = new LicenseService();
        $results = $service->processRenewals();

        // Must report failure, NOT success
        $this->assertSame(0, $results['success']);
        $this->assertSame(1, $results['failed']);

        $license->refresh();
        // Expiry must NOT have been extended
        $this->assertEquals($pastExpiry->toIso8601String(), $license->expires_at->toIso8601String());
        // Grace period must have started (7 days from now)
        $this->assertNotNull($license->grace_expires_at);
        $this->assertTrue($license->grace_expires_at->isFuture());

        // Audit log must record failure/grace started
        $this->assertTrue(
            AuditLog::where('action', 'license_renewal_failed_grace_started')
                ->where('auditable_id', $license->id)
                ->exists()
        );
    }

    public function test_renewal_fails_when_payment_record_status_is_failed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        ProductPrice::create([
            'product_id'     => $product->id,
            'currency'       => 'USD',
            'amount'         => 30,
            'type'           => 'subscription',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-FAIL-1',
            'user_id'      => $user->id,
            'total_amount' => 30,
            'currency'     => 'USD',
            'status'       => 'pending',
        ]);

        Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_failed_1',
            'amount'         => 30,
            'status'         => 'failed',
        ]);

        $pastExpiry = Carbon::now()->subMinute();
        $license = License::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'order_id'         => $order->id,
            'license_key_hash' => hash('sha256', 'FAIL-KEY-salt'),
            'secret_salt'      => 'salt',
            'type'             => 'subscription',
            'status'           => 'active',
            'auto_renew'       => true,
            'expires_at'       => $pastExpiry,
            'next_billing_at'  => $pastExpiry,
        ]);

        $service = new LicenseService();
        $results = $service->processRenewals();

        $this->assertSame(0, $results['success']);
        $this->assertSame(1, $results['failed']);

        $license->refresh();
        $this->assertEquals($pastExpiry->toIso8601String(), $license->expires_at->toIso8601String());
        $this->assertNotNull($license->grace_expires_at);
    }

    public function test_renewal_succeeds_when_verified_payment_record_exists(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        ProductPrice::create([
            'product_id'     => $product->id,
            'currency'       => 'USD',
            'amount'         => 100,
            'type'           => 'subscription',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-VERIFIED-1',
            'user_id'      => $user->id,
            'total_amount' => 100,
            'currency'     => 'USD',
            'status'       => 'completed',
        ]);

        Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_verified_1',
            'amount'         => 100,
            'status'         => 'verified',
        ]);

        $license = License::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'order_id'         => $order->id,
            'license_key_hash' => hash('sha256', 'VERIFIED-KEY-salt'),
            'secret_salt'      => 'salt',
            'type'             => 'subscription',
            'status'           => 'active',
            'auto_renew'       => true,
            'expires_at'       => Carbon::now()->subMinute(),
            'next_billing_at'  => Carbon::now()->subMinute(),
            'grace_expires_at' => Carbon::now()->addDays(2),
        ]);

        $service = new LicenseService();
        $results = $service->processRenewals();

        $this->assertSame(1, $results['success']);
        $this->assertSame(0, $results['failed']);

        $license->refresh();
        $this->assertTrue($license->expires_at->isFuture());
        $this->assertTrue($license->next_billing_at->isFuture());
        $this->assertNull($license->grace_expires_at);

        $this->assertTrue(
            AuditLog::where('action', 'license_renewed')
                ->where('auditable_id', $license->id)
                ->exists()
        );
    }

    public function test_renewal_charges_bkash_recurring_subscription_when_configured(): void
    {
        config()->set('app.url', 'https://checkout.example.com');
        config()->set('services.bkash.app_key', 'test-app-key');
        config()->set('services.bkash.app_secret', 'test-app-secret');

        Http::fake([
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/token/grant' => Http::response([
                'status_code' => '0000',
                'id_token'    => 'test-token',
            ], 200),
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/checkout/create' => Http::response([
                'statusCode' => '0000',
                'bkashURL'   => 'https://bkash.example/checkout',
                'paymentID'  => 'bkash-renew-live-1',
            ], 200),
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/checkout/execute' => Http::response([
                'transactionStatus' => 'Completed',
                'trxID'             => 'trx_live_123',
                'paymentID'         => 'bkash-renew-live-1',
            ], 200),
        ]);

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type'           => 'subscription',
            'amount'         => 15,
            'currency'       => 'BDT',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number'   => 'ORD-BKASH-LIVE-1',
            'user_id'        => $user->id,
            'total_amount'   => 15,
            'currency'       => 'BDT',
            'status'         => 'completed',
            'payment_method' => 'bkash',
        ]);

        $license = License::create([
            'user_id'                 => $user->id,
            'product_id'              => $product->id,
            'order_id'                => $order->id,
            'license_key_hash'        => hash('sha256', 'BKASH-LIVE-salt'),
            'secret_salt'             => 'salt',
            'type'                    => 'subscription',
            'status'                  => 'active',
            'auto_renew'              => true,
            'expires_at'              => Carbon::now()->subMinute(),
            'next_billing_at'         => Carbon::now()->subMinute(),
            'gateway_subscription_id' => 'bkash_sub_live_1',
        ]);

        $service = new LicenseService();
        $results = $service->processRenewals();

        $this->assertSame(1, $results['success']);
        $this->assertSame(0, $results['failed']);

        $license->refresh();
        $this->assertTrue($license->expires_at->isFuture());
        $this->assertTrue($license->next_billing_at->isFuture());

        $this->assertDatabaseHas('payments', [
            'order_id'       => $order->id,
            'gateway'        => 'bkash',
            'transaction_id' => 'bkash-renew-live-1',
            'status'         => 'verified',
        ]);
    }
}
