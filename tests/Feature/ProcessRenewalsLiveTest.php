<?php

namespace Tests\Feature;

use App\Jobs\ProcessLicenseRenewal;
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
        ]);
        $order->forceFill(['created_at' => $orderTime, 'updated_at' => $orderTime])->save();

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
            'expires_at'       => $pastExpiry,
            'next_billing_at'  => $pastExpiry,
        ]);
        $license->forceFill(['created_at' => $orderTime, 'updated_at' => $orderTime])->save();

        // Initial purchase payment was consumed at order fulfillment time (applied_at is set)
        $payment = Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_initial_order_purchase',
            'amount'         => 50,
            'status'         => 'verified',
            'applied_at'     => $orderTime,
        ]);
        $payment->forceFill(['created_at' => $orderTime, 'updated_at' => $orderTime])->save();

        $service = new LicenseService();
        $results = $service->processRenewals();

        // Must FAIL: initial purchase payment is consumed and cannot satisfy renewal
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

        $order = Order::create([
            'order_number' => 'ORD-CONSECUTIVE-1',
            'user_id'      => $user->id,
            'total_amount' => 50,
            'currency'     => 'USD',
            'status'       => 'completed',
        ]);

        $license = License::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'order_id'         => $order->id,
            'license_key_hash' => hash('sha256', 'CONSEC-KEY-salt'),
            'secret_salt'      => 'salt',
            'type'             => 'subscription',
            'status'           => 'active',
            'auto_renew'       => true,
            'expires_at'       => Carbon::now()->subMinute(),
            'next_billing_at'  => Carbon::now()->subMinute(),
        ]);

        // Payment 1 created for Cycle 1
        $payment1 = Payment::create([
            'order_id'       => $order->id,
            'license_id'     => $license->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_cycle_1_payment',
            'amount'         => 50,
            'status'         => 'verified',
            'applied_at'     => null, // unconsumed
        ]);

        $service = new LicenseService();

        // Cycle 1 Renewal succeeds
        $res1 = $service->processRenewals();
        $this->assertSame(1, $res1['success']);
        $license->refresh();
        $payment1->refresh();
        $this->assertNotNull($payment1->applied_at, 'Payment 1 must be marked consumed upon use.');

        // Advance license to Cycle 2 due date
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
            'license_id'     => $license->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_cycle_2_payment',
            'amount'         => 50,
            'status'         => 'verified',
            'applied_at'     => null,
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
            'expires_at'       => Carbon::now()->subMinute(),
            'next_billing_at'  => Carbon::now()->subMinute(),
        ]);

        // Verified unconsumed payment
        Payment::create([
            'order_id'       => $order->id,
            'license_id'     => $license->id,
            'user_id'        => $user->id,
            'gateway'        => 'stripe',
            'transaction_id' => 'tx_idemp_payment',
            'amount'         => 100,
            'status'         => 'verified',
            'applied_at'     => null,
        ]);

        $service = new LicenseService();

        // First run -> successfully renews
        $res1 = $service->processRenewals();
        $this->assertSame(1, $res1['success']);
        $license->refresh();
        $firstExpiry = $license->expires_at->copy();

        // Second run immediately -> license is not due, must NOT extend again
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

    public function test_job_path_grants_grace_once_then_expires_and_no_infinite_grace(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type'           => 'subscription',
            'amount'         => 30,
            'currency'       => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-JOB-LIFECYCLE-1',
            'user_id'      => $user->id,
            'total_amount' => 30,
            'currency'     => 'USD',
            'status'       => 'completed',
        ]);

        $license = License::create([
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'order_id'         => $order->id,
            'license_key_hash' => hash('sha256', 'JOB-KEY-salt'),
            'secret_salt'      => 'salt',
            'type'             => 'subscription',
            'status'           => 'active',
            'auto_renew'       => true,
            'expires_at'       => Carbon::now()->subMinute(),
            'next_billing_at'  => Carbon::now()->subMinute(),
            'grace_expires_at' => null, // Initial: null
        ]);

        $job = new ProcessLicenseRenewal($license);

        // Run 1: Payment fails -> Grace granted (7 days) and notice sent
        $job->handle();
        $license->refresh();
        $this->assertNotNull($license->grace_expires_at);
        $this->assertTrue($license->grace_expires_at->isFuture());
        $this->assertSame('active', $license->status);
        Mail::assertSentCount(1);

        $originalGraceExpiry = $license->grace_expires_at->copy();

        // Run 2: While grace is still active, must NOT grant a new grace window and NOT send duplicate email
        $job->handle();
        $license->refresh();
        $this->assertEquals($originalGraceExpiry->toIso8601String(), $license->grace_expires_at->toIso8601String());
        Mail::assertSentCount(1);

        // Advance time: Grace period expires
        $license->update(['grace_expires_at' => Carbon::now()->subHour()]);

        // Run 3: Grace has expired -> Must mark license as expired, disable auto_renew, and send expiration notice
        $job->handle();
        $license->refresh();
        $this->assertSame('expired', $license->status);
        $this->assertFalse((bool) $license->auto_renew);
        Mail::assertSentCount(2);

        // Run 4: Repeated run after expiry -> Must do nothing, must NOT grant new grace
        $job->handle();
        $license->refresh();
        $this->assertSame('expired', $license->status);
        $this->assertFalse((bool) $license->auto_renew);
        Mail::assertSentCount(2); // No new mail
    }

    public function test_suspended_license_is_never_renewed_or_reactivated(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-SUSP-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'SUSP-KEY-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'suspended', // Suspended!
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        // Even with a verified payment linked
        Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'stripe',
            'transaction_id' => 'tx_susp_pay',
            'amount' => 50,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();
        $this->assertFalse($service->renewLicense($license));

        $license->refresh();
        $this->assertSame('suspended', $license->status);
        $this->assertTrue(
            AuditLog::where('action', 'license_renewal_blocked_disallowed_status')
                ->where('auditable_id', $license->id)
                ->exists()
        );
    }

    public function test_revoked_license_is_never_renewed_or_reactivated(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-REVOKED-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'REVOKED-KEY-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'revoked', // Revoked!
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'stripe',
            'transaction_id' => 'tx_rev_pay',
            'amount' => 50,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();
        $this->assertFalse($service->renewLicense($license));

        $license->refresh();
        $this->assertSame('revoked', $license->status);
    }

    public function test_cancelled_license_is_never_renewed_or_reactivated(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-CANCELLED-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'CANCELLED-KEY-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'cancelled', // Cancelled!
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'stripe',
            'transaction_id' => 'tx_canc_pay',
            'amount' => 50,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();
        $this->assertFalse($service->renewLicense($license));

        $license->refresh();
        $this->assertSame('cancelled', $license->status);
    }

    public function test_renewal_rejects_payment_for_different_license(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-MULTI-LIC-1',
            'user_id' => $user->id,
            'total_amount' => 100,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $licenseA = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'LIC-A-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        $licenseB = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'LIC-B-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        // Payment is explicitly linked to License B only
        Payment::create([
            'order_id' => $order->id,
            'license_id' => $licenseB->id,
            'user_id' => $user->id,
            'gateway' => 'stripe',
            'transaction_id' => 'tx_for_b_only',
            'amount' => 50,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();

        // License A must FAIL: no payment linked to License A
        $this->assertFalse($service->renewLicense($licenseA));

        // License B must SUCCEED with its linked payment
        $this->assertTrue($service->renewLicense($licenseB));
    }

    public function test_renewal_rejects_payment_already_consumed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-CONSUMED-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'CONSUMED-KEY-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        // Payment is verified, but applied_at is ALREADY set (consumed)
        Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'stripe',
            'transaction_id' => 'tx_already_consumed',
            'amount' => 50,
            'status' => 'verified',
            'applied_at' => Carbon::now()->subHour(),
        ]);

        $service = new LicenseService();
        $this->assertFalse($service->renewLicense($license));
    }

    public function test_renewal_rejects_payment_with_insufficient_amount(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 100, // Price is $100
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-INSUFFICIENT-1',
            'user_id' => $user->id,
            'total_amount' => 100,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'INSUFFICIENT-KEY-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        // Payment is only $20 (insufficient)
        Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'stripe',
            'transaction_id' => 'tx_too_low',
            'amount' => 20,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();
        $this->assertFalse($service->renewLicense($license));
    }

    public function test_advance_renewal_payment_made_before_expiry_is_accepted(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-ADVANCE-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'ADVANCE-KEY-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        // Payment was made 3 days before expiry (advance renewal)
        $payment = Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'stripe',
            'transaction_id' => 'tx_advance_renewal',
            'amount' => 50,
            'status' => 'verified',
            'applied_at' => null,
        ]);
        $payment->forceFill(['created_at' => Carbon::now()->subDays(3)])->save();

        $service = new LicenseService();
        $this->assertTrue($service->renewLicense($license));

        $license->refresh();
        $this->assertTrue($license->expires_at->isFuture());
        $payment->refresh();
        $this->assertNotNull($payment->applied_at);
    }

    public function test_stripe_managed_subscription_is_bypassed_by_renewal_service_and_job(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-STRIPE-SUB-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'STRIPE-SUB-KEY-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
            'gateway_subscription_id' => 'sub_stripe_real_123', // Stripe sub
        ]);

        $service = new LicenseService();

        // In batch processRenewals: query excludes sub_% subscriptions
        $res = $service->processRenewals();
        $this->assertSame(0, $res['success']);
        $this->assertSame(0, $res['failed']); // Not queried

        // In direct call: renewLicense guards and returns false
        $this->assertFalse($service->renewLicense($license));

        // In Job: ProcessLicenseRenewal guards and does nothing
        $job = new ProcessLicenseRenewal($license);
        $job->handle();
        $license->refresh();
        $this->assertNull($license->grace_expires_at, 'Stripe subscription should not enter grace via cron.');
    }

    public function test_renewal_aligns_next_billing_at_to_new_expiry_date(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 100,
            'currency' => 'USD',
            'billing_period' => 30, // 30 days
        ]);

        $order = Order::create([
            'order_number' => 'ORD-NEXT-BILLING-1',
            'user_id' => $user->id,
            'total_amount' => 100,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $pastExpiry = Carbon::now()->subMinute();
        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'NEXT-BILL-KEY-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => $pastExpiry,
            'next_billing_at' => $pastExpiry,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'stripe',
            'transaction_id' => 'tx_align_billing',
            'amount' => 100,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();
        $this->assertTrue($service->renewLicense($license));

        $license->refresh();
        // next_billing_at MUST be equal to new expires_at (when renewal is due), NOT two periods ahead!
        $this->assertEquals($license->expires_at->toIso8601String(), $license->next_billing_at->toIso8601String());
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
            'license_id'     => $license->id,
            'gateway'        => 'bkash',
            'transaction_id' => 'bkash-renew-live-1',
            'status'         => 'verified',
        ]);
    }

    public function test_renewal_rejects_payment_with_mismatched_currency(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        // Order made in BDT instead of USD
        $order = Order::create([
            'order_number' => 'ORD-CURR-MISMATCH-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'BDT',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'CURR-MISMATCH-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'manual',
            'transaction_id' => 'tx_mismatch_curr',
            'amount' => 50,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();
        $this->assertFalse($service->renewLicense($license));
        $this->assertNull(Payment::where('transaction_id', 'tx_mismatch_curr')->first()->applied_at);
    }

    public function test_renewal_payment_consumed_atomically_preventing_concurrent_double_renewal(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-CONCURRENT-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'CONCURRENT-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'manual',
            'transaction_id' => 'tx_atomic_consume',
            'amount' => 50,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();

        // First process acquires and consumes payment inside transaction
        $this->assertTrue($service->renewLicense($license));
        $payment->refresh();
        $this->assertNotNull($payment->applied_at);

        // Second concurrent process attempting to qualify the same payment gets false
        $this->assertFalse($service->qualifyOrChargeRenewalPayment($license, $product->prices()->first()));
    }

    public function test_renewal_fails_closed_when_price_cannot_be_resolved(): void
    {
        $user = User::factory()->create();
        // Product has NO prices configured
        $product = Product::factory()->create();

        $order = Order::create([
            'order_number' => 'ORD-NO-PRICE-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'NO-PRICE-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'manual',
            'transaction_id' => 'tx_no_price_pay',
            'amount' => 50,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();

        // Must fail closed when price cannot be resolved
        $this->assertFalse($service->qualifyOrChargeRenewalPayment($license, null));
        $this->assertNull($payment->fresh()->applied_at);
        $this->assertFalse($service->renewLicense($license));
        $this->assertNull($payment->fresh()->applied_at);
    }

    public function test_renewal_fails_closed_when_price_has_no_amount(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 0.0, // Invalid amount
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-ZERO-PRICE-1',
            'user_id' => $user->id,
            'total_amount' => 0,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'ZERO-PRICE-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'manual',
            'transaction_id' => 'tx_zero_price_pay',
            'amount' => 0,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();

        // Must fail closed when price amount is 0 or null
        $this->assertFalse($service->qualifyOrChargeRenewalPayment($license, $price));
        $this->assertNull($payment->fresh()->applied_at);
        $this->assertFalse($service->renewLicense($license));
        $this->assertNull($payment->fresh()->applied_at);
    }

    public function test_renewal_fails_closed_when_price_has_no_currency(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 50.0,
            'currency' => '', // Empty currency
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-NO-CURR-PRICE-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'NO-CURR-PRICE-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'manual',
            'transaction_id' => 'tx_no_curr_price_pay',
            'amount' => 50,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();

        // Must fail closed when currency is empty or missing
        $this->assertFalse($service->qualifyOrChargeRenewalPayment($license, $price));
        $this->assertNull($payment->fresh()->applied_at);
        $this->assertFalse($service->renewLicense($license));
        $this->assertNull($payment->fresh()->applied_at);
    }

    public function test_charge_recurring_subscription_alias_enforces_price_and_currency_checks(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-ALIAS-TEST-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'ALIAS-KEY-salt'),
            'secret_salt' => 'salt',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
        ]);

        // 1. Payment with insufficient amount (25 < 50)
        $shortPayment = Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => $user->id,
            'gateway' => 'manual',
            'transaction_id' => 'tx_alias_short',
            'amount' => 25,
            'status' => 'verified',
            'applied_at' => null,
        ]);

        $service = new LicenseService();

        // Must reject insufficient payment even when called via alias
        $this->assertFalse($service->chargeRecurringSubscription($license));
        $this->assertNull($shortPayment->fresh()->applied_at);

        // Update payment to full required amount
        $shortPayment->update(['amount' => 50]);

        // Must now succeed and consume payment
        $this->assertTrue($service->chargeRecurringSubscription($license));
        $this->assertNotNull($shortPayment->fresh()->applied_at);
    }

    public function test_proof_stripe_checkout_initial_payment_cannot_renew_subscription(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-STRIPE-PROOF-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'online',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'price' => 50,
            'license_type' => 'subscription',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'gateway' => 'stripe',
            'transaction_id' => 'cs_test_session_123',
            'amount' => 50,
            'status' => 'pending',
        ]);

        // Fulfill Stripe order as done in WebhookController / success callback
        $fulfillment = app(\App\Services\OrderFulfillmentService::class)->fulfillOrder($order, [
            'transaction_id' => 'pi_stripe_test_123',
            'gateway_response' => ['id' => 'cs_test_session_123'],
        ]);

        $license = $fulfillment['license'];
        $this->assertInstanceOf(License::class, $license);
        $this->assertEquals('subscription', $license->type);
        $payment->refresh();
        $this->assertEquals('verified', $payment->status);
        $this->assertNotNull($payment->applied_at, 'Stripe initial payment must have applied_at set upon fulfillment');

        // Advance time to next_billing_at
        $due = Carbon::now()->subMinute();
        $license->update([
            'status' => 'active',
            'expires_at' => $due,
            'next_billing_at' => $due,
        ]);

        // Run renewal command
        $results = (new LicenseService())->processRenewals();
        $this->assertSame(0, $results['success']);
        $this->assertSame(1, $results['failed']);

        $license->refresh();
        $this->assertEquals($due->toIso8601String(), $license->expires_at->toIso8601String());
        $this->assertNotNull($license->grace_expires_at);
    }

    public function test_proof_bkash_checkout_initial_payment_cannot_renew_subscription(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'BDT',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-BKASH-PROOF-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'BDT',
            'status' => 'pending',
            'payment_method' => 'online',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'price' => 50,
            'license_type' => 'subscription',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT_PROOF',
            'amount' => 50,
            'status' => 'pending',
            'gateway_response' => ['paymentID' => 'TR0012PAYMENT_PROOF'],
        ]);

        // Fulfill via bKash flow as done in callback / execute
        $fulfillment = app(\App\Services\OrderFulfillmentService::class)->fulfillOrder($order, [
            'transaction_id' => 'TRX_BKASH_9999',
            'gateway_response' => ['paymentID' => 'TR0012PAYMENT_PROOF', 'trxID' => 'TRX_BKASH_9999', 'transactionStatus' => 'Completed'],
        ]);

        $license = $fulfillment['license'];
        $this->assertInstanceOf(License::class, $license);
        $this->assertEquals('subscription', $license->type);
        $payment->refresh();
        $this->assertEquals('verified', $payment->status);
        $this->assertNotNull($payment->applied_at, 'bKash initial payment must have applied_at set upon fulfillment');

        // Advance time to next_billing_at
        $due = Carbon::now()->subMinute();
        $license->update([
            'status' => 'active',
            'expires_at' => $due,
            'next_billing_at' => $due,
        ]);

        // Run renewal command
        $results = (new LicenseService())->processRenewals();
        $this->assertSame(0, $results['success']);
        $this->assertSame(1, $results['failed']);

        $license->refresh();
        $this->assertEquals($due->toIso8601String(), $license->expires_at->toIso8601String());
        $this->assertNotNull($license->grace_expires_at);
    }

    public function test_proof_offline_receipt_admin_approval_initial_payment_cannot_renew_subscription(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-OFFLINE-PROOF-1',
            'user_id' => $customer->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'awaiting_payment',
            'payment_method' => 'offline',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'price' => 50,
            'license_type' => 'subscription',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'gateway' => 'offline',
            'amount' => 50,
            'status' => 'pending',
            'payment_proof_path' => 'receipts/test.png',
        ]);

        // Admin approves payment
        $response = $this->actingAs($admin)->postJson("/api/v1/admin/payments/{$payment->id}/verify", [
            'action' => 'approve',
            'notes' => 'Proof verified',
        ]);
        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('verified', $payment->status);
        $this->assertNotNull($payment->applied_at, 'Offline receipt payment must have applied_at set upon admin approval');

        $license = License::where('order_id', $order->id)->first();
        $this->assertNotNull($license);
        $this->assertEquals('subscription', $license->type);

        // Advance time to next_billing_at
        $due = Carbon::now()->subMinute();
        $license->update([
            'status' => 'active',
            'expires_at' => $due,
            'next_billing_at' => $due,
        ]);

        // Run renewal command
        $results = (new LicenseService())->processRenewals();
        $this->assertSame(0, $results['success']);
        $this->assertSame(1, $results['failed']);

        $license->refresh();
        $this->assertEquals($due->toIso8601String(), $license->expires_at->toIso8601String());
        $this->assertNotNull($license->grace_expires_at);
    }

    public function test_proof_order_fulfillment_initial_payment_cannot_renew_subscription(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'type' => 'subscription',
            'amount' => 50,
            'currency' => 'USD',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-FULFILL-PROOF-1',
            'user_id' => $user->id,
            'total_amount' => 50,
            'currency' => 'USD',
            'status' => 'pending',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'price' => 50,
            'license_type' => 'subscription',
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'gateway' => 'manual',
            'transaction_id' => 'tx_direct_fulfill',
            'amount' => 50,
            'status' => 'pending',
        ]);

        $fulfillment = app(\App\Services\OrderFulfillmentService::class)->fulfillOrder($order);
        $license = $fulfillment['license'];
        $this->assertInstanceOf(License::class, $license);
        $this->assertEquals('subscription', $license->type);

        $payment->refresh();
        $this->assertEquals('verified', $payment->status);
        $this->assertNotNull($payment->applied_at, 'Order fulfillment must mark payment applied_at');

        // Advance time to next_billing_at
        $due = Carbon::now()->subMinute();
        $license->update([
            'status' => 'active',
            'expires_at' => $due,
            'next_billing_at' => $due,
        ]);

        // Run renewal command
        $results = (new LicenseService())->processRenewals();
        $this->assertSame(0, $results['success']);
        $this->assertSame(1, $results['failed']);

        $license->refresh();
        $this->assertEquals($due->toIso8601String(), $license->expires_at->toIso8601String());
        $this->assertNotNull($license->grace_expires_at);
    }
}

