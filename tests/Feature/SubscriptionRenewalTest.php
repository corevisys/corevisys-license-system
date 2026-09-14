<?php

namespace Tests\Feature;

use App\Jobs\ProcessLicenseRenewal;
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
        $order = Order::create([
            'order_number' => 'SUB-TEST',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'SUBKEY' . 'salt-subkey'),
            'secret_salt' => 'salt-subkey',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => Carbon::now()->subMinute(), // Expired
            'next_billing_at' => Carbon::now()->subMinute(), // Due
            'gateway_subscription_id' => 'sub_123'
        ]);

        // 2. Run Service directly (or command)
        $service = new LicenseService();
        $results = $service->processRenewals();

        // 3. Verify
        $this->assertEquals(1, $results['success']);

        $license->refresh();
        $this->assertTrue($license->expires_at->isFuture());
        $this->assertTrue($license->next_billing_at->isFuture());
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

    public function test_bkash_recurring_charge_successfully_renews_license()
    {
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
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/checkout/execute' => Http::response([
                'transactionStatus' => 'Completed',
                'trxID' => 'trx_123',
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

        $this->assertTrue($license->expires_at->isFuture());
        $this->assertTrue($license->next_billing_at->isFuture());
        $this->assertSame('active', $license->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway' => 'bkash',
            'transaction_id' => 'bkash-payment-1',
            'status' => 'verified',
        ]);
    }

    public function test_bkash_recurring_charge_failure_starts_grace_and_notifies_customer()
    {
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
                'paymentID' => 'bkash-payment-failed',
            ], 200),
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/checkout/execute' => Http::response([
                'transactionStatus' => 'Failed',
                'paymentID' => 'bkash-payment-failed',
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

        $this->assertNotNull($license->grace_expires_at);
        $this->assertTrue($license->grace_expires_at->isFuture());
        $this->assertSame('active', $license->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway' => 'bkash',
            'transaction_id' => 'bkash-payment-failed',
            'status' => 'failed',
        ]);
    }

    public function test_bkash_recurring_charge_is_idempotent_for_successful_repeat_attempts()
    {
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
            'https://tokenized.sandbox.bka.sh/v1.2.0-beta/checkout/execute' => Http::response([
                'transactionStatus' => 'Completed',
                'trxID' => 'trx_duplicated',
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

        $this->assertDatabaseCount('payments', 1);
    }

}
