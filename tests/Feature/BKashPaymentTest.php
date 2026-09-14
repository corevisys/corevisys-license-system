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
}
