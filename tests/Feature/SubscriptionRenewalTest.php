<?php

namespace Tests\Feature;

use App\Jobs\ProcessLicenseRenewal;
use App\Mail\BkashRenewalPaymentLink;
use App\Models\License;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\OrderFulfillmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class SubscriptionRenewalTest extends TestCase
{
    use RefreshDatabase;

    public function test_renewal_command_extends_expiry()
    {
        // 1. Setup
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type'           => 'subscription',
            'amount'         => 10,
            'currency'       => 'USD',
            'billing_period' => 30,
        ]);
        $order = Order::create([
            'order_number' => 'SUB-TEST',
            'user_id'      => $user->id,
            'total_amount' => 10,
            'currency'     => 'USD',
            'status'       => 'completed'
        ]);

        $license = License::create([
            'user_id'        => $user->id,
            'product_id'     => $product->id,
            'order_id'       => $order->id,
            'license_key_hash' => hash('sha256', 'SUBKEY' . 'salt-subkey'),
            'secret_salt'    => 'salt-subkey',
            'type'           => 'subscription',
            'status'         => 'active',
            'auto_renew'     => true,
            'expires_at'     => Carbon::now()->subMinute(), // Expired
            'next_billing_at' => Carbon::now()->subMinute(), // Due
            // No gateway_subscription_id: non-Stripe path processed by cron
        ]);
        $license->forceFill(['created_at' => Carbon::now()->subDays(30)])->save();

        // Renewal-cycle payment explicitly linked to the license (FIX-005 requirement).
        Payment::create([
            'order_id'       => $order->id,
            'user_id'        => $user->id,
            'license_id'     => $license->id,
            'gateway'        => 'manual',
            'transaction_id' => 'in_sub_test_123',
            'amount'         => 10,
            'status'         => 'verified',
        ]);

        // 2. Run Service directly (or command)
        $service = new LicenseService();
        $results = $service->processRenewals();

        // 3. Verify
        $this->assertEquals(1, $results['success']);

        $license->refresh();
        $this->assertTrue($license->expires_at->isFuture());
        // next_billing_at must equal expires_at (billing alignment fix 1f)
        $this->assertEquals(
            $license->expires_at->format('Y-m-d'),
            $license->next_billing_at->format('Y-m-d')
        );
    }

    public function test_order_fulfillment_rolls_back_when_license_generation_fails()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = Order::create([
            'order_number' => 'ROLLBACK-1',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'stripe',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => 10,
            'license_type' => 'full',
        ]);

        Payment::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_id' => 'pi_test',
            'amount' => 10.00,
            'status' => 'pending',
            'gateway_response' => [],
        ]);

        $licenseService = Mockery::mock(LicenseService::class);
        $licenseService->shouldReceive('createLicense')->once()->andThrow(new \Exception('license generation failed'));

        $service = new OrderFulfillmentService($licenseService);

        $this->expectException(\Exception::class);
        $service->fulfillOrder($order, []);

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame('pending', $order->fresh()->payment->status);
    }

    public function test_bkash_renewal_creates_customer_checkout_without_extending_license()
    {
        Mail::fake();
        config()->set('app.url', 'https://checkout.example.com');
        config()->set('services.bkash.app_key', 'test-app-key');
        config()->set('services.bkash.app_secret', 'test-app-secret');

        Http::fake([
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/token/grant' => Http::response([
                'status_code' => '0000',
                'id_token' => 'test-token',
            ], 200),
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/checkout/create' => Http::response([
                'statusCode' => '0000',
                'bkashURL' => 'https://bkash.example/checkout',
                'paymentID' => 'bkash-payment-1',
            ], 200),
        ]);

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 10,
            'currency' => 'BDT',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'BKASH-RENEW-1',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'BDT',
            'status' => 'completed',
            'payment_method' => 'bkash',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'BKASH-RENEW' . 'salt-bkash-renew'),
            'secret_salt' => 'salt-bkash-renew',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
            'gateway_subscription_id' => 'bkash_123',
        ]);

        $job = new ProcessLicenseRenewal($license);
        $job->handle();

        $license->refresh();
        $renewalOrder = Order::where('license_id', $license->id)->where('type', 'renewal')->firstOrFail();

        $this->assertTrue($license->expires_at->isPast());
        $this->assertTrue($license->next_billing_at->isPast());
        $this->assertSame('awaiting_payment', $renewalOrder->status);
        $this->assertSame('active', $license->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $renewalOrder->id,
            'gateway' => 'bkash',
            'transaction_id' => 'bkash-payment-1',
            'status' => 'pending',
        ]);
        Mail::assertSent(BkashRenewalPaymentLink::class, 1);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/checkout/execute'));
    }

    public function test_bkash_checkout_creation_failure_marks_attempt_failed_and_does_not_extend()
    {
        Mail::fake();
        config()->set('app.url', 'https://checkout.example.com');
        config()->set('services.bkash.app_key', 'test-app-key');
        config()->set('services.bkash.app_secret', 'test-app-secret');

        Http::fake([
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/token/grant' => Http::response([
                'status_code' => '0000',
                'id_token' => 'test-token',
            ], 200),
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/checkout/create' => Http::response(['statusCode' => 'Failed'], 500),
        ]);

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 10,
            'currency' => 'BDT',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'BKASH-RENEW-FAILED',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'BDT',
            'status' => 'completed',
            'payment_method' => 'bkash',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'BKASH-RENEW-FAIL' . 'salt-bkash-fail'),
            'secret_salt' => 'salt-bkash-fail',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
            'gateway_subscription_id' => 'bkash_456',
        ]);

        $job = new ProcessLicenseRenewal($license);
        $job->handle();

        $license->refresh();
        $renewalOrder = Order::where('license_id', $license->id)->where('type', 'renewal')->firstOrFail();

        $this->assertNotNull($license->grace_expires_at);
        $this->assertTrue($license->grace_expires_at->isFuture());
        $this->assertTrue($license->expires_at->isPast());
        $this->assertSame('active', $license->status);
        $this->assertSame('cancelled', $renewalOrder->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $renewalOrder->id,
            'gateway' => 'bkash',
            'status' => 'failed',
        ]);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/checkout/execute'));
        Mail::assertNotSent(BkashRenewalPaymentLink::class);
    }

    public function test_bkash_renewal_checkout_retries_are_idempotent()
    {
        Mail::fake();
        config()->set('app.url', 'https://checkout.example.com');
        config()->set('services.bkash.app_key', 'test-app-key');
        config()->set('services.bkash.app_secret', 'test-app-secret');

        Http::fake([
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/token/grant' => Http::response([
                'status_code' => '0000',
                'id_token' => 'test-token',
            ], 200),
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/checkout/create' => Http::response([
                'statusCode' => '0000',
                'bkashURL' => 'https://bkash.example/checkout',
                'paymentID' => 'bkash-payment-duplicate',
            ], 200),
        ]);

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 10,
            'currency' => 'BDT',
            'billing_period' => 30,
        ]);

        $order = Order::create([
            'order_number' => 'BKASH-RENEW-DUPLICATE',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'BDT',
            'status' => 'completed',
            'payment_method' => 'bkash',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'BKASH-RENEW-DUP' . 'salt-bkash-dup'),
            'secret_salt' => 'salt-bkash-dup',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(),
            'next_billing_at' => Carbon::now()->subMinute(),
            'gateway_subscription_id' => 'bkash_789',
        ]);

        $firstJob = new ProcessLicenseRenewal($license);
        $firstJob->handle();

        $secondJob = new ProcessLicenseRenewal($license);
        $secondJob->handle();

        $this->assertSame(1, Order::where('license_id', $license->id)->where('type', 'renewal')->count());
        $this->assertSame(1, \App\Models\Payment::where('gateway', 'bkash')->count());
        Http::assertSentCount(2); // one token grant and one create request
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/checkout/execute'));
        Mail::assertSent(BkashRenewalPaymentLink::class, 1);
    }

}
