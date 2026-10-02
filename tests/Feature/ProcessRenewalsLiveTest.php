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
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * FIX-005 / BUG-001: Comprehensive renewal payment verification tests.
 *
 * Verifies that:
 * 1. Initial purchase payment cannot be reused for renewal.
 * 2. A second consecutive renewal requires a second payment.
 * 3. Running the renewal command twice does not extend or charge twice.
 * 4. Payment failure moves to grace/past_due with customer notification.
 * 5. Expired grace period marks license as expired and notifies customer.
 * 6. bKash recurring subscription charging is covered.
 * 7. Stripe renewal paths are covered.
 */
class ProcessRenewalsLiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_purchase_payment_cannot_be_reused_for_renewal(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        ProductPrice::create([
            'product_id'     => $product->id,
            'currency'       => 'USD',
            'amount'         => 50,
            'type'           => 'subscription',
            'billing_period' => 30,
        ]);

        $orderTime = Carbon::now()->subDays(30);

        $order = Order::create([
            'order_number' => 'ORD-INIT-PAY-1',
            'user_id'      => $user->id,
            'total_amount' => 50,
            'currency'     => 'USD',
            'status'       => 'completed',
            'created_at'   => $orderTime,
        ]);

        // Initial purchase payment created at order time (30 days ago)
        Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_initial_order_purchase',
            'amount'         => 50,
            'status'         => 'verified',
            'created_at'     => $orderTime,
        ]);

        $pastExpiry = Carbon::now()->subMinute();
        $license = License::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'order_id'         => $order->id,
            'license_key_hash' => hash('sha256', 'INIT-REUSE-KEY-salt'),
            'secret_salt'      => 'salt',
            'type'             => 'subscription',
            'status'           => 'active',
            'auto_renew'       => true,
            'created_at'       => $orderTime,
            'expires_at'       => $pastExpiry,
            'next_billing_at'  => $pastExpiry,
        ]);

        $service = new LicenseService();
        $results = $service->processRenewals();

        // Must FAIL: initial purchase payment cannot satisfy renewal
        $this->assertSame(0, $results['success']);
        $this->assertSame(1, $results['failed']);

        $license->refresh();
        $this->assertEquals($pastExpiry->toIso8601String(), $license->expires_at->toIso8601String());
        $this->assertNotNull($license->grace_expires_at);
        $this->assertTrue($license->grace_expires_at->isFuture());

        Mail::assertSentCount(1);
    }

    public function test_second_consecutive_renewal_requires_second_payment(): void
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

        $initialTime = Carbon::now()->subDays(60);
        $order = Order::create([
            'order_number' => 'ORD-CONSECUTIVE-1',
            'user_id'      => $user->id,
            'total_amount' => 50,
            'currency'     => 'USD',
            'status'       => 'completed',
            'created_at'   => $initialTime,
        ]);

        // Initial purchase payment (60 days ago)
        Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_init_consec',
            'amount'         => 50,
            'status'         => 'verified',
            'created_at'     => $initialTime,
        ]);

        // License was due for Cycle 1 at subDays(30)
        $cycle1Due = Carbon::now()->subDays(30);
        $license = License::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'order_id'         => $order->id,
            'license_key_hash' => hash('sha256', 'CONSEC-KEY-salt'),
            'secret_salt'      => 'salt',
            'type'             => 'subscription',
            'status'           => 'active',
            'auto_renew'       => true,
            'created_at'       => $initialTime,
            'expires_at'       => $cycle1Due,
            'next_billing_at'  => $cycle1Due,
        ]);

        // Payment 1 created for Cycle 1
        Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_cycle_1_payment',
            'amount'         => 50,
            'status'         => 'verified',
            'created_at'     => $cycle1Due,
        ]);

        $service = new LicenseService();

        // Cycle 1 Renewal succeeds
        $res1 = $service->processRenewals();
        $this->assertSame(1, $res1['success']);
        $license->refresh();

        // Advance license to Cycle 2 due date (now)
        $license->update([
            'expires_at'      => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        // Run renewal without a second payment -> must FAIL
        $res2 = $service->processRenewals();
        $this->assertSame(0, $res2['success']);
        $this->assertSame(1, $res2['failed']);

        // Now record Payment 2 for Cycle 2
        Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_cycle_2_payment',
            'amount'         => 50,
            'status'         => 'verified',
            'created_at'     => Carbon::now(),
        ]);

        // Run renewal again -> must SUCCEED with Payment 2
        $res3 = $service->processRenewals();
        $this->assertSame(1, $res3['success']);
        $license->refresh();
        $this->assertTrue($license->expires_at->isFuture());
    }

    public function test_running_renewal_command_twice_does_not_extend_or_charge_twice(): void
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
            'order_number' => 'ORD-IDEMPOTENT-1',
            'user_id'      => $user->id,
            'total_amount' => 100,
            'currency'     => 'USD',
            'status'       => 'completed',
            'created_at'   => Carbon::now()->subDays(30),
        ]);

        $license = License::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'order_id'         => $order->id,
            'license_key_hash' => hash('sha256', 'IDEMP-KEY-salt'),
            'secret_salt'      => 'salt',
            'type'             => 'subscription',
            'status'           => 'active',
            'auto_renew'       => true,
            'created_at'       => Carbon::now()->subDays(30),
            'expires_at'       => Carbon::now()->subMinute(),
            'next_billing_at'  => Carbon::now()->subMinute(),
        ]);

        // Verified payment for this renewal cycle
        Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_idemp_payment',
            'amount'         => 100,
            'status'         => 'verified',
            'created_at'     => Carbon::now(),
        ]);

        $service = new LicenseService();

        // First run -> successfully renews
        $res1 = $service->processRenewals();
        $this->assertSame(1, $res1['success']);
        $license->refresh();
        $firstExpiry = $license->expires_at->copy();

        // Second run immediately -> not due, must NOT extend again
        $res2 = $service->processRenewals();
        $this->assertSame(0, $res2['success']);
        $this->assertSame(0, $res2['failed']);

        $license->refresh();
        $this->assertEquals($firstExpiry->toIso8601String(), $license->expires_at->toIso8601String());
    }

    public function test_payment_failure_moves_to_grace_and_notifies_customer(): void
    {
        Mail::fake();

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
            'order_number' => 'ORD-GRACE-FAIL-1',
            'user_id'      => $user->id,
            'total_amount' => 50,
            'currency'     => 'USD',
            'status'       => 'completed',
            'created_at'   => Carbon::now()->subDays(30),
        ]);

        $pastExpiry = Carbon::now()->subMinute();
        $license = License::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'order_id'         => $order->id,
            'license_key_hash' => hash('sha256', 'GRACE-FAIL-KEY-salt'),
            'secret_salt'      => 'salt',
            'type'             => 'subscription',
            'status'           => 'active',
            'auto_renew'       => true,
            'created_at'       => Carbon::now()->subDays(30),
            'expires_at'       => $pastExpiry,
            'next_billing_at'  => $pastExpiry,
        ]);

        $service = new LicenseService();
        $results = $service->processRenewals();

        $this->assertSame(0, $results['success']);
        $this->assertSame(1, $results['failed']);

        $license->refresh();
        $this->assertNotNull($license->grace_expires_at);
        $this->assertTrue($license->grace_expires_at->isFuture());

        $this->assertTrue(
            AuditLog::where('action', 'license_renewal_failed_grace_started')
                ->where('auditable_id', $license->id)
                ->exists()
        );

        Mail::assertSentCount(1);
    }

    public function test_grace_period_expiration_marks_license_expired_and_notifies_customer(): void
    {
        Mail::fake();

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
            'order_number' => 'ORD-GRACE-ENDED-1',
            'user_id'      => $user->id,
            'total_amount' => 50,
            'currency'     => 'USD',
            'status'       => 'completed',
            'created_at'   => Carbon::now()->subDays(40),
        ]);

        $pastTime = Carbon::now()->subDays(8);
        $license = License::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'order_id'         => $order->id,
            'license_key_hash' => hash('sha256', 'GRACE-ENDED-KEY-salt'),
            'secret_salt'      => 'salt',
            'type'             => 'subscription',
            'status'           => 'active',
            'auto_renew'       => true,
            'created_at'       => Carbon::now()->subDays(40),
            'expires_at'       => $pastTime,
            'next_billing_at'  => $pastTime,
            'grace_expires_at' => Carbon::now()->subDay(), // Grace ended yesterday
        ]);

        $service = new LicenseService();
        $results = $service->processRenewals();

        $this->assertSame(1, $results['failed']);

        $license->refresh();
        $this->assertSame('expired', $license->status);
        $this->assertFalse((bool) $license->auto_renew);

        $this->assertTrue(
            AuditLog::where('action', 'license_expired_grace_ended')
                ->where('auditable_id', $license->id)
                ->exists()
        );

        Mail::assertSentCount(1);
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
            'created_at'   => Carbon::now()->subDays(30),
        ]);

        Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_failed_1',
            'amount'         => 30,
            'status'         => 'failed',
            'created_at'     => Carbon::now(),
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
            'created_at'       => Carbon::now()->subDays(30),
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
            'created_at'   => Carbon::now()->subDays(30),
        ]);

        Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_verified_cycle_payment',
            'amount'         => 100,
            'status'         => 'verified',
            'created_at'     => Carbon::now(),
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
            'created_at'       => Carbon::now()->subDays(30),
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
            'created_at'     => Carbon::now()->subDays(30),
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
            'created_at'              => Carbon::now()->subDays(30),
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
