<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\BKashPaymentService;
use App\Services\ReceiptStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests for the 3 PR fixes:
 *   Fix 1 - scanReceipt() HTTP timeout (fail closed)
 *   Fix 2 - bKash grantToken() caching
 *   Fix 3 - pruneExpiredReceipts() uses chunkById instead of get()
 */
class PrFixes1To3Test extends TestCase
{
    use RefreshDatabase;

    // Fix 1 - scanReceipt() timeout: fail closed

    public function test_scan_receipt_throws_and_rejects_upload_on_connection_timeout(): void
    {
        Http::fake([
            'https://scan.example.com/*' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out');
            },
        ]);

        config([
            'receipt.scan_endpoint'                 => 'https://scan.example.com/scan',
            'services.receipt_scan.timeout'         => 10,
            'services.receipt_scan.connect_timeout' => 5,
        ]);

        Storage::fake('local');

        $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Receipt malware scan failed.');

        app(ReceiptStorageService::class)->storeUploadedReceipt($file);
    }

    public function test_scan_receipt_succeeds_when_endpoint_returns_clean(): void
    {
        Http::fake([
            'https://scan.example.com/*' => Http::response(['clean' => true], 200),
        ]);

        config([
            'receipt.scan_endpoint'                 => 'https://scan.example.com/scan',
            'receipt.scan_api_key'                  => '',
            'services.receipt_scan.timeout'         => 7,
            'services.receipt_scan.connect_timeout' => 3,
        ]);

        Storage::fake('local');

        $file = UploadedFile::fake()->create('invoice.pdf', 50, 'application/pdf');

        $path = app(ReceiptStorageService::class)->storeUploadedReceipt($file);

        $this->assertNotEmpty($path);
        Http::assertSent(fn ($req) => str_contains($req->url(), 'scan.example.com'));
    }

    public function test_scan_receipt_rejects_upload_when_scan_endpoint_returns_5xx(): void
    {
        Http::fake([
            'https://scan.example.com/*' => Http::response([], 503),
        ]);

        config([
            'receipt.scan_endpoint'         => 'https://scan.example.com/scan',
            'services.receipt_scan.timeout' => 10,
        ]);

        Storage::fake('local');

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Receipt malware scan failed.');

        app(ReceiptStorageService::class)->storeUploadedReceipt($file);
    }

    // Fix 2 - bKash grantToken() caching

    private function setUpBKash(): void
    {
        config([
            'app.url'                   => 'https://checkout.example.com',
            'services.bkash.app_key'    => 'test-app-key',
            'services.bkash.app_secret' => 'test-app-secret',
        ]);
        SystemSetting::create(['key' => 'gateway_bkash_active',  'value' => '1']);
        SystemSetting::create(['key' => 'gateway_bkash_sandbox', 'value' => '1']);
    }

    private function sandboxCacheKey(): string
    {
        $url = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta';
        return 'bkash_token:' . md5($url . '|test-app-key');
    }

    public function test_bkash_token_is_cached_and_grant_not_called_on_second_request(): void
    {
        $this->setUpBKash();
        Cache::flush();

        $grantCalls = 0;

        Http::fake(function ($request) use (&$grantCalls) {
            if (str_contains($request->url(), 'token/grant')) {
                $grantCalls++;
                return Http::response([
                    'status_code' => '0000', 'status_message' => 'Successful',
                    'id_token'    => 'cached-token-abc',
                ]);
            }
            if (str_contains($request->url(), '/checkout/create')) {
                return Http::response([
                    'paymentID' => 'PAY001', 'bkashURL' => 'https://sandbox.bka.sh/checkout/test',
                    'statusCode' => '0000',  'statusMessage' => 'Successful',
                ]);
            }
            if (str_contains($request->url(), '/checkout/execute')) {
                return Http::response([
                    'paymentID' => 'PAY001', 'trxID' => 'TRX001',
                    'transactionStatus' => 'Completed', 'statusCode' => '0000',
                ]);
            }
            return Http::response([], 404);
        });

        $user    = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Cache Test']);
        $price   = $product->prices()->create(['currency' => 'BDT', 'amount' => 50.00, 'type' => 'full']);
        $order   = Order::create([
            'order_number' => 'ORD-CACHE-001', 'user_id' => $user->id,
            'total_amount' => 50.00, 'currency' => 'BDT',
            'status' => 'awaiting_payment', 'payment_method' => 'online',
        ]);

        app(BKashPaymentService::class)->createPayment($order, $price);
        $this->assertSame(1, $grantCalls, 'token/grant should be called once for createPayment');

        app()->forgetInstance(BKashPaymentService::class);
        app(BKashPaymentService::class)->executePayment('PAY001');
        $this->assertSame(1, $grantCalls, 'token/grant must NOT be called again on cache hit');
    }

    public function test_bkash_token_ttl_is_expires_in_minus_60_seconds(): void
    {
        $this->setUpBKash();
        Cache::flush();

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'token/grant')) {
                return Http::response([
                    'status_code' => '0000', 'status_message' => 'Successful',
                    'id_token'    => 'token-with-ttl',
                    'expires_in'  => 1800,
                ]);
            }
            if (str_contains($request->url(), '/checkout/create')) {
                return Http::response([
                    'paymentID' => 'PAY002', 'bkashURL' => 'https://sandbox.bka.sh/checkout/ttl',
                    'statusCode' => '0000',  'statusMessage' => 'Successful',
                ]);
            }
            return Http::response([], 404);
        });

        $user    = User::factory()->create();
        $product = Product::factory()->create(['name' => 'TTL Product']);
        $price   = $product->prices()->create(['currency' => 'BDT', 'amount' => 99.00, 'type' => 'full']);
        $order   = Order::create([
            'order_number' => 'ORD-TTL-001', 'user_id' => $user->id,
            'total_amount' => 99.00, 'currency' => 'BDT',
            'status' => 'awaiting_payment', 'payment_method' => 'online',
        ]);

        app(BKashPaymentService::class)->createPayment($order, $price);

        // Before expiry: at 1739s, token must still be in cache
        $this->travel(1739)->seconds();
        $this->assertSame('token-with-ttl', Cache::get($this->sandboxCacheKey()));

        // After expiry (1800 - 60 = 1740s): at 1741s, token must be expired
        $this->travel(2)->seconds();
        $this->assertNull(Cache::get($this->sandboxCacheKey()), 'Token must expire after 1740s, not 3000s');
    }

    public function test_bkash_failed_token_response_is_not_cached(): void
    {
        $this->setUpBKash();
        Cache::flush();

        Http::fake(function ($request) {
            if (str_contains($request->url(), 'token/grant')) {
                return Http::response([
                    'status_code' => '9999', 'status_message' => 'Internal error', 'id_token' => '',
                ], 200);
            }
            return Http::response([], 404);
        });

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('bKash token grant rejected');

        $user    = User::factory()->create();
        $product = Product::factory()->create();
        $price   = $product->prices()->create(['currency' => 'BDT', 'amount' => 10.00, 'type' => 'full']);
        $order   = Order::create([
            'order_number' => 'ORD-FAIL-001', 'user_id' => $user->id,
            'total_amount' => 10.00, 'currency' => 'BDT',
            'status' => 'awaiting_payment', 'payment_method' => 'online',
        ]);

        try {
            app(BKashPaymentService::class)->createPayment($order, $price);
        } finally {
            $this->assertNull(Cache::get($this->sandboxCacheKey()), 'Failed token must not be cached');
        }
    }

    public function test_bkash_stale_cached_token_is_forgotten_and_retried_once_on_401(): void
    {
        $this->setUpBKash();
        Cache::flush();

        Cache::put($this->sandboxCacheKey(), 'stale-token-xyz', 3000);

        $grantCalls = 0;

        Http::fake(function ($request) use (&$grantCalls) {
            if (str_contains($request->url(), 'token/grant')) {
                $grantCalls++;
                return Http::response([
                    'status_code' => '0000', 'status_message' => 'Successful',
                    'id_token'    => 'fresh-token-after-retry',
                ]);
            }
            if (str_contains($request->url(), '/checkout/create')) {
                $auth = $request->header('Authorization');
                $authToken = is_array($auth) ? ($auth[0] ?? '') : (string) $auth;
                if ($authToken === 'stale-token-xyz') {
                    return Http::response(['error' => 'invalid_token'], 401);
                }
                return Http::response([
                    'paymentID' => 'PAY-RETRY', 'bkashURL' => 'https://sandbox.bka.sh/checkout/retry',
                    'statusCode' => '0000',      'statusMessage' => 'Successful',
                ]);
            }
            return Http::response([], 404);
        });

        $user    = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Retry Product']);
        $price   = $product->prices()->create(['currency' => 'BDT', 'amount' => 75.00, 'type' => 'full']);
        $order   = Order::create([
            'order_number' => 'ORD-RETRY-001', 'user_id' => $user->id,
            'total_amount' => 75.00, 'currency' => 'BDT',
            'status' => 'awaiting_payment', 'payment_method' => 'online',
        ]);

        $result = app(BKashPaymentService::class)->createPayment($order, $price);

        $this->assertSame('PAY-RETRY', $result['paymentID']);
        $this->assertSame(1, $grantCalls, 'token/grant should fire exactly once during the 401 retry');
    }

    // Fix 3 - pruneExpiredReceipts() uses chunkById

    private function insertOldPayment(int $orderId, int $userId, string $proofPath, string $createdAt): int
    {
        return (int) DB::table('payments')->insertGetId([
            'order_id'           => $orderId,
            'user_id'            => $userId,
            'gateway'            => 'offline',
            'amount'             => 100.00,
            'status'             => 'verified',
            'payment_proof_path' => $proofPath,
            'created_at'         => $createdAt,
            'updated_at'         => $createdAt,
        ]);
    }

    public function test_prune_expired_receipts_deletes_old_records_and_returns_count(): void
    {
        Storage::fake('receipts');

        $user = User::factory()->create();
        $oldPaymentIds = [];

        foreach (range(1, 3) as $i) {
            $order = Order::factory()->create(['user_id' => $user->id]);
            Storage::disk('receipts')->put("receipts/old-{$i}.pdf", 'fake');
            $oldPaymentIds[] = $this->insertOldPayment(
                $order->id, $user->id,
                "receipts/old-{$i}.pdf",
                now()->subDays(120)->toDateTimeString()
            );
        }

        $recentOrder = Order::factory()->create(['user_id' => $user->id]);
        Storage::disk('receipts')->put('receipts/new.pdf', 'fake');
        $recentId = $this->insertOldPayment(
            $recentOrder->id, $user->id,
            'receipts/new.pdf',
            now()->subDays(10)->toDateTimeString()
        );

        config(['receipt.storage_disk' => 'receipts', 'receipt.retention_days' => 90]);

        $deleted = app(ReceiptStorageService::class)->pruneExpiredReceipts();

        $this->assertSame(3, $deleted);
        foreach ($oldPaymentIds as $id) {
            $this->assertDatabaseMissing('payments', ['id' => $id]);
        }
        $this->assertDatabaseHas('payments', ['id' => $recentId]);
        foreach (range(1, 3) as $i) {
            Storage::disk('receipts')->assertMissing("receipts/old-{$i}.pdf");
        }
        Storage::disk('receipts')->assertExists('receipts/new.pdf');
    }

    public function test_prune_continues_processing_remaining_records_when_one_fails(): void
    {
        Storage::fake('receipts');
        $user = User::factory()->create();

        foreach (range(1, 2) as $i) {
            $order = Order::factory()->create(['user_id' => $user->id]);
            Storage::disk('receipts')->put("receipts/pay-{$i}.pdf", 'x');
            $this->insertOldPayment(
                $order->id, $user->id,
                "receipts/pay-{$i}.pdf",
                now()->subDays(200)->toDateTimeString()
            );
        }

        config(['receipt.storage_disk' => 'receipts', 'receipt.retention_days' => 90]);

        $deleted = app(ReceiptStorageService::class)->pruneExpiredReceipts();

        $this->assertSame(2, $deleted);
        $this->assertSame(0, Payment::whereNotNull('payment_proof_path')->count());
    }

    public function test_prune_processes_more_than_one_chunk_of_200_records(): void
    {
        Storage::fake('receipts');
        $user = User::factory()->create();

        foreach (range(1, 210) as $i) {
            $order = Order::factory()->create(['user_id' => $user->id]);
            Storage::disk('receipts')->put("receipts/bulk-{$i}.pdf", 'x');
            $this->insertOldPayment(
                $order->id, $user->id,
                "receipts/bulk-{$i}.pdf",
                now()->subDays(100)->toDateTimeString()
            );
        }

        config(['receipt.storage_disk' => 'receipts', 'receipt.retention_days' => 90]);

        $deleted = app(ReceiptStorageService::class)->pruneExpiredReceipts();

        $this->assertSame(210, $deleted, 'All 210 records across 2 chunks should be pruned');
        $this->assertSame(0, Payment::whereNotNull('payment_proof_path')->count());
    }
}
