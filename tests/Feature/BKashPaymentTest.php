<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\BKashPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BKashPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'https://checkout.example.com',
            'services.bkash.app_key' => 'test-app-key',
            'services.bkash.app_secret' => 'test-app-secret',
        ]);

        SystemSetting::create(['key' => 'gateway_bkash_active', 'value' => '1']);
        SystemSetting::create(['key' => 'gateway_bkash_sandbox', 'value' => '1']);
    }

    protected function fakeBkashApi(bool $completed = true): void
    {
        Http::fake(function ($request) use ($completed) {
            if (str_contains($request->url(), 'token/grant')) {
                return Http::response([
                    'status_code' => '0000',
                    'status_message' => 'Successful',
                    'id_token' => 'test-id-token',
                ]);
            }

            if (str_contains($request->url(), '/create')) {
                return Http::response([
                    'paymentID' => 'TR0012PAYMENT',
                    'bkashURL' => 'https://sandbox.bka.sh/checkout/test',
                    'statusCode' => '0000',
                    'statusMessage' => 'Successful',
                ]);
            }

            if (str_contains($request->url(), '/execute')) {
                return Http::response([
                    'paymentID' => 'TR0012PAYMENT',
                    'trxID' => 'TRX123456',
                    'transactionStatus' => $completed ? 'Completed' : 'Initiated',
                    'amount' => '99.00',
                    'statusCode' => '0000',
                ]);
            }

            if (str_contains($request->url(), '/checkout/payment/status')) {
                return Http::response([
                    'paymentID' => 'TR0012PAYMENT',
                    'trxID' => 'TRX123456',
                    'transactionStatus' => $completed ? 'Completed' : 'Initiated',
                    'amount' => '99.00',
                    'statusCode' => '0000',
                ]);
            }

            return Http::response(['statusCode' => '4040'], 404);
        });
    }

    public function test_bkash_service_ignores_database_secret_fallbacks()
    {
        config([
            'services.bkash.app_key' => null,
            'services.bkash.app_secret' => null,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('bKash App Key / App Secret not configured.');

        $this->fakeBkashApi();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-TEST0002',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);

        app(BKashPaymentService::class)->createPayment($order, $product->prices->first());
    }

    public function test_create_payment_calls_grant_and_create_and_returns_bkash_url()
    {
        $this->fakeBkashApi();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-TEST0001',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);

        $response = app(BKashPaymentService::class)->createPayment($order, $product->prices->first());

        $this->assertEquals('TR0012PAYMENT', $response['paymentID']);
        $this->assertEquals('https://sandbox.bka.sh/checkout/test', $response['bkashURL']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'token/grant')
                && $request['app_key'] === 'test-app-key';
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/checkout/create')
                && $request['amount'] === '99.00'
                && $request['currency'] === 'BDT'
                && $request['merchantInvoiceNumber'] === 'ORD-TEST0001';
        });
    }

    public function test_api_order_store_with_bkash_returns_url_and_stores_payment()
    {
        $this->fakeBkashApi();

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'bKash Product', 'is_active' => true]);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/orders/create', [
            'product_id' => $product->id,
            'gateway' => 'bkash',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('bkash_url', 'https://sandbox.bka.sh/checkout/test');

        $order = Order::findOrFail($response->json('order.id'));

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT',
            'status' => 'pending',
        ]);
    }

    public function test_bkash_callback_executes_and_fulfills_order()
    {
        $this->fakeBkashApi(completed: true);

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Callback Product']);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-CB0001',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'price' => 99.00,
            'license_type' => 'full',
        ]);
        $order->payments()->create([
            'user_id' => $user->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT',
            'amount' => 99.00,
            'status' => 'pending',
        ]);

        $response = $this->get('/orders/bkash/callback?paymentID=TR0012PAYMENT');

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('payments', ['id' => $order->payment->id, 'status' => 'verified', 'transaction_id' => 'TRX123456']);
        $this->assertDatabaseHas('licenses', ['order_id' => $order->id]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/execute') && $request['paymentID'] === 'TR0012PAYMENT';
        });
    }

    public function test_bkash_callback_does_not_fulfill_when_not_completed()
    {
        $this->fakeBkashApi(completed: false);

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Not Completed']);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-CB0002',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'price' => 99.00,
            'license_type' => 'full',
        ]);
        $order->payments()->create([
            'user_id' => $user->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT',
            'amount' => 99.00,
            'status' => 'pending',
        ]);

        $response = $this->get('/orders/bkash/callback?paymentID=TR0012PAYMENT');

        $response->assertRedirect(route('orders'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'awaiting_payment']);
        $this->assertDatabaseMissing('licenses', ['order_id' => $order->id]);
    }

    public function test_create_payment_converts_non_bdt_order_amount_to_bdt()
    {
        // 120 BDT = 1 USD (stored as rate_to_base with 4 decimal places)
        \App\Models\ExchangeRate::create(['currency' => 'BDT', 'rate_to_base' => 0.0083]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'token/grant')) {
                return Http::response([
                    'status_code' => '0000',
                    'status_message' => 'Successful',
                    'id_token' => 'test-id-token',
                ]);
            }

            return Http::response([
                'paymentID' => 'TR0012PAYMENT',
                'bkashURL' => 'https://sandbox.bka.sh/checkout/test',
                'statusCode' => '0000',
                'statusMessage' => 'Successful',
            ]);
        });

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'USD Product']);
        $product->prices()->create(['currency' => 'USD', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-USD0001',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'USD',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);

        app(BKashPaymentService::class)->createPayment($order, $product->prices->first());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/create') && $request['amount'] === '11927.71';
        });
    }

    public function test_create_payment_throws_when_callback_url_is_not_public_https()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('bKash callback URL must use a public HTTPS domain');

        config(['app.url' => 'http://localhost']);
        Http::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Local URL Product']);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-LOCAL0001',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);

        app(BKashPaymentService::class)->createPayment($order, $product->prices->first());
    }

    public function test_create_payment_throws_when_bdt_rate_missing_for_non_bdt_order()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('BDT exchange rate is not configured.');

        Http::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'USD No Rate']);
        $product->prices()->create(['currency' => 'USD', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-USD0002',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'USD',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);

        app(BKashPaymentService::class)->createPayment($order, $product->prices->first());
    }

    public function test_api_execute_bkash_confirms_and_fulfills()
    {
        $this->fakeBkashApi(completed: true);

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'API Execute']);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-EX0001',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'price' => 99.00,
            'license_type' => 'full',
        ]);
        $order->payments()->create([
            'user_id' => $user->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT',
            'amount' => 99.00,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/orders/bkash/execute', [
            'payment_id' => 'TR0012PAYMENT',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('transaction_id', 'TRX123456');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('licenses', ['order_id' => $order->id]);
    }

    public function test_bkash_callback_ignores_query_parameters_and_verifies_server_side()
    {
        // Gateway returns non-completed server response despite query params claiming success
        $this->fakeBkashApi(completed: false);

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Untrusted Query Params Product']);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-UNTRUST-01',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'price' => 99.00,
            'license_type' => 'full',
        ]);
        $order->payments()->create([
            'user_id' => $user->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT',
            'amount' => 99.00,
            'status' => 'pending',
        ]);

        // Malicious or spoofed query parameters attempting to claim success and forge trxID
        $response = $this->get('/orders/bkash/callback?paymentID=TR0012PAYMENT&status=success&trxID=FAKE_TRX_9999');

        $response->assertRedirect(route('orders'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'awaiting_payment']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseMissing('licenses', ['order_id' => $order->id]);
    }

    public function test_bkash_callback_ignores_cancel_query_parameter_when_server_confirms_completed()
    {
        // Server API confirms payment is completed
        $this->fakeBkashApi(completed: true);

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Cancel Spoof Product']);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-CANCEL-SPOOF',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'price' => 99.00,
            'license_type' => 'full',
        ]);
        $order->payments()->create([
            'user_id' => $user->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT',
            'amount' => 99.00,
            'status' => 'pending',
        ]);

        // Tampered query parameter saying status=cancel, but server-side confirms Completed
        $response = $this->get('/orders/bkash/callback?paymentID=TR0012PAYMENT&status=cancel');

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'verified', 'transaction_id' => 'TRX123456']);
        $this->assertDatabaseHas('licenses', ['order_id' => $order->id]);
    }

    public function test_bkash_callback_falls_back_to_query_payment_if_execute_fails_and_fulfills_if_completed()
    {
        // Execute returns 500/fails, but queryPayment returns Completed
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'token/grant')) {
                return Http::response([
                    'status_code' => '0000',
                    'status_message' => 'Successful',
                    'id_token' => 'test-id-token',
                ]);
            }

            if (str_contains($request->url(), '/execute')) {
                return Http::response(['statusCode' => '2029', 'statusMessage' => 'Duplicate execution'], 500);
            }

            if (str_contains($request->url(), '/checkout/payment/status')) {
                return Http::response([
                    'paymentID' => 'TR0012PAYMENT',
                    'trxID' => 'TRX_FROM_QUERY_STATUS',
                    'transactionStatus' => 'Completed',
                    'amount' => '99.00',
                    'statusCode' => '0000',
                ]);
            }

            return Http::response(['statusCode' => '4040'], 404);
        });

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Query Fallback Product']);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-QUERY-FALLBACK',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'price' => 99.00,
            'license_type' => 'full',
        ]);
        $order->payments()->create([
            'user_id' => $user->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT',
            'amount' => 99.00,
            'status' => 'pending',
        ]);

        $response = $this->get('/orders/bkash/callback?paymentID=TR0012PAYMENT');

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'verified', 'transaction_id' => 'TRX_FROM_QUERY_STATUS']);
        $this->assertDatabaseHas('licenses', ['order_id' => $order->id]);
    }

    public function test_bkash_callback_is_idempotent_on_duplicate_invocations_no_duplicate_license()
    {
        $this->fakeBkashApi(completed: true);

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Idempotent Callback Product']);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-IDEM-01',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'price' => 99.00,
            'license_type' => 'full',
        ]);
        $order->payments()->create([
            'user_id' => $user->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT',
            'amount' => 99.00,
            'status' => 'pending',
        ]);

        // First callback invocation
        $firstResponse = $this->get('/orders/bkash/callback?paymentID=TR0012PAYMENT');
        $firstResponse->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertEquals(1, \App\Models\License::where('order_id', $order->id)->count());

        // Second callback invocation with same paymentID
        $secondResponse = $this->get('/orders/bkash/callback?paymentID=TR0012PAYMENT');
        $secondResponse->assertRedirect(route('dashboard'));

        // License count must strictly remain 1 (no duplicate license)
        $this->assertEquals(1, \App\Models\License::where('order_id', $order->id)->count());
    }

    public function test_bkash_callback_is_idempotent_on_renewal_orders_no_duplicate_renewal()
    {
        $this->fakeBkashApi(completed: true);

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Renewal Product']);
        $price = $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full', 'billing_period' => 30]);

        $initialOrder = Order::create([
            'order_number' => 'ORD-INIT-01',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'completed',
            'payment_method' => 'online',
            'type' => 'purchase',
        ]);
        $initialOrder->items()->create([
            'product_id' => $product->id,
            'price' => 99.00,
            'license_type' => 'full',
        ]);
        $license = app(\App\Services\LicenseService::class)->createLicense($initialOrder, $product, 'full');
        $initialExpiry = now()->addDays(15);
        $license->update(['expires_at' => $initialExpiry]);

        // Create renewal order pointing to existing license
        $renewalOrder = Order::create([
            'order_number' => 'ORD-REN-01',
            'user_id' => $user->id,
            'license_id' => $license->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
            'type' => 'renewal',
        ]);
        $renewalOrder->items()->create([
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'price' => 99.00,
            'license_type' => 'full',
        ]);
        $renewalOrder->payments()->create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT',
            'amount' => 99.00,
            'status' => 'pending',
        ]);

        // First callback invocation: renews license once (+30 days)
        $firstResponse = $this->get('/orders/bkash/callback?paymentID=TR0012PAYMENT');
        $firstResponse->assertRedirect(route('dashboard'));

        $license->refresh();
        $expectedRenewedExpiry = $license->expires_at;
        $this->assertTrue($expectedRenewedExpiry->greaterThan($initialExpiry));

        // Second callback invocation: must NOT renew again (+0 days)
        $secondResponse = $this->get('/orders/bkash/callback?paymentID=TR0012PAYMENT');
        $secondResponse->assertRedirect(route('dashboard'));

        $license->refresh();
        $this->assertEquals(
            $expectedRenewedExpiry->toIso8601String(),
            $license->expires_at->toIso8601String(),
            'License expiry was extended a second time on repeated callback'
        );
        $this->assertEquals(1, \App\Models\License::where('user_id', $user->id)->count());
    }

    public function test_api_execute_bkash_is_idempotent_on_repeated_calls()
    {
        $this->fakeBkashApi(completed: true);

        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'API Idempotent']);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);

        $order = Order::create([
            'order_number' => 'ORD-API-IDEM',
            'user_id' => $user->id,
            'total_amount' => 99.00,
            'currency' => 'BDT',
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'price' => 99.00,
            'license_type' => 'full',
        ]);
        $order->payments()->create([
            'user_id' => $user->id,
            'gateway' => 'bkash',
            'transaction_id' => 'TR0012PAYMENT',
            'amount' => 99.00,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        // First call
        $firstResponse = $this->postJson('/api/v1/orders/bkash/execute', [
            'payment_id' => 'TR0012PAYMENT',
        ]);
        $firstResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('transaction_id', 'TRX123456');

        // Second call
        $secondResponse = $this->postJson('/api/v1/orders/bkash/execute', [
            'payment_id' => 'TR0012PAYMENT',
        ]);
        $secondResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('transaction_id', 'TRX123456');

        $this->assertEquals(1, \App\Models\License::where('order_id', $order->id)->count());
    }

    public function test_order_fulfillment_service_skips_already_completed_renewal_order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Direct Fulfillment Product']);
        $product->prices()->create(['currency' => 'BDT', 'amount' => 50.00, 'type' => 'full', 'billing_period' => 30]);

        $initialOrder = Order::create([
            'order_number' => 'ORD-DIRECT-INIT',
            'user_id' => $user->id,
            'total_amount' => 50.00,
            'currency' => 'BDT',
            'status' => 'completed',
            'payment_method' => 'online',
            'type' => 'purchase',
        ]);
        $initialOrder->items()->create([
            'product_id' => $product->id,
            'price' => 50.00,
            'license_type' => 'full',
        ]);
        $license = app(\App\Services\LicenseService::class)->createLicense($initialOrder, $product, 'full');
        $fixedExpiry = now()->addDays(20);
        $license->update(['expires_at' => $fixedExpiry]);

        $renewalOrder = Order::create([
            'order_number' => 'ORD-DIRECT-REN',
            'user_id' => $user->id,
            'license_id' => $license->id,
            'total_amount' => 50.00,
            'currency' => 'BDT',
            'status' => 'completed', // Already completed
            'payment_method' => 'online',
            'type' => 'renewal',
        ]);
        $renewalOrder->items()->create([
            'product_id' => $product->id,
            'price' => 50.00,
            'license_type' => 'full',
        ]);

        $service = app(\App\Services\OrderFulfillmentService::class);
        $result = $service->fulfillOrder($renewalOrder);

        $this->assertNotNull($result);
        $this->assertEquals($license->id, $result['license']->id);
        $this->assertEquals(
            $fixedExpiry->toIso8601String(),
            $license->fresh()->expires_at->toIso8601String(),
            'License expiry changed on an already completed renewal order'
        );
    }
}
