<?php

namespace Tests\Feature;

use App\Jobs\ProcessLicenseRenewal;
use App\Mail\BkashRenewalPaymentLink;
use App\Models\License;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\BkashRenewalCheckoutService;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;
use Tests\TestCase;

class BkashRenewalCheckoutTest extends TestCase
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

    public function test_due_bkash_renewal_creates_pending_checkout_email_without_executing_or_extending(): void
    {
        Mail::fake();
        $license = $this->dueBkashLicense();
        $originalExpiry = $license->expires_at->copy();
        $this->fakeProvider();

        $results = app(LicenseService::class)->processRenewals();

        $this->assertSame(['success' => 0, 'failed' => 1], $results);
        $license->refresh();
        $order = Order::where('license_id', $license->id)->where('type', 'renewal')->firstOrFail();
        $payment = $order->payments()->firstOrFail();

        $this->assertSame($originalExpiry->timestamp, $license->expires_at->timestamp);
        $this->assertSame('awaiting_payment', $order->status);
        $this->assertSame($license->id, $payment->license_id);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('BKASH-RENEWAL-PAYMENT', $payment->transaction_id);
        $this->assertSame('https://checkout.example.com/pay/renewal', $payment->gateway_response['bkashURL']);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $license->product_id,
            'product_price_id' => $license->product->prices()->first()->id,
        ]);

        Mail::assertSent(BkashRenewalPaymentLink::class, function (BkashRenewalPaymentLink $mail) use ($order) {
            $mail->assertSeeInText('https://checkout.example.com/pay/renewal');
            $mail->assertSeeInText('including entering your PIN');

            return $mail->order->id === $order->id;
        });
        Http::assertSent(fn ($request) => str_contains($request->url(), '/checkout/create'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/checkout/execute'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/checkout/payment/status'));
    }

    public function test_unapplied_original_bkash_payment_cannot_authorize_the_next_renewal(): void
    {
        Mail::fake();
        $license = $this->dueBkashLicense();
        $originalExpiry = $license->expires_at->timestamp;
        Payment::where('order_id', $license->order_id)->update(['applied_at' => null]);
        $this->fakeProvider();

        app(LicenseService::class)->processRenewals();

        $this->assertSame($originalExpiry, $license->fresh()->expires_at->timestamp);
        $this->assertSame('awaiting_payment', Order::where('license_id', $license->id)
            ->where('type', 'renewal')->value('status'));
        $this->assertNull(Payment::where('order_id', $license->order_id)->value('applied_at'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/checkout/execute'));
    }

    public function test_repeated_due_job_reuses_order_payment_checkout_and_email(): void
    {
        Mail::fake();
        $license = $this->dueBkashLicense();
        $this->fakeProvider();

        (new ProcessLicenseRenewal($license))->handle();
        (new ProcessLicenseRenewal($license->fresh()))->handle();

        $this->assertSame(1, Order::where('license_id', $license->id)->where('type', 'renewal')->count());
        $this->assertSame(1, Payment::where('license_id', $license->id)
            ->where('gateway', 'bkash')
            ->whereHas('order', fn ($query) => $query->where('type', 'renewal'))
            ->count());
        Http::assertSentCount(2);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/checkout/execute'));
        Mail::assertSent(BkashRenewalPaymentLink::class, 1);
    }

    public function test_failed_payment_link_email_can_be_retried(): void
    {
        $license = $this->dueBkashLicense();
        $this->fakeProvider();
        $mailManager = app('mail.manager');
        Mail::shouldReceive('to')
            ->once()
            ->with($license->user->email)
            ->andReturn(new class
            {
                public function send($mailable): void
                {
                    throw new \RuntimeException('mail unavailable');
                }
            });

        app(BkashRenewalCheckoutService::class)->createOrReuse($license);

        $order = Order::where('license_id', $license->id)->where('type', 'renewal')->firstOrFail();
        $this->assertNull($order->renewal_link_email_sent_at);

        Mail::swap(new MailFake($mailManager));
        app(BkashRenewalCheckoutService::class)->createOrReuse($license->fresh());

        $this->assertNotNull($order->fresh()->renewal_link_email_sent_at);
        Mail::assertSent(BkashRenewalPaymentLink::class, 1);
    }

    public function test_successful_callback_verifies_and_renews_linked_license_only_once(): void
    {
        Mail::fake();
        $license = $this->dueBkashLicense();
        $originalExpiry = $license->expires_at->copy();
        $this->fakeProvider(completed: true);
        (new ProcessLicenseRenewal($license))->handle();

        $first = $this->get('/orders/bkash/callback?paymentID=BKASH-RENEWAL-PAYMENT&status=success&trxID=FORGED');
        $first->assertRedirect(route('dashboard'));

        $license->refresh();
        $order = Order::where('license_id', $license->id)->where('type', 'renewal')->firstOrFail();
        $this->assertTrue($license->expires_at->isFuture());
        $this->assertNotSame($originalExpiry->timestamp, $license->expires_at->timestamp);
        $this->assertSame($license->expires_at->timestamp, $license->next_billing_at->timestamp);
        $this->assertSame('completed', $order->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'verified',
            'transaction_id' => 'BKASH-RENEWAL-TRX',
        ]);
        $this->assertSame(1, License::where('id', $license->id)->count());

        $second = $this->get('/orders/bkash/callback?paymentID=BKASH-RENEWAL-PAYMENT');
        $second->assertRedirect(route('dashboard'));
        $this->assertSame($license->expires_at->timestamp, $license->fresh()->expires_at->timestamp);
        Http::assertSentCount(3); // grant/create plus exactly one server-side execute
    }

    public function test_incomplete_or_cancelled_callback_does_not_extend_license(): void
    {
        Mail::fake();
        $license = $this->dueBkashLicense();
        $originalExpiry = $license->expires_at->timestamp;
        $this->fakeProvider(completed: false);
        (new ProcessLicenseRenewal($license))->handle();

        $this->get('/orders/bkash/callback?paymentID=BKASH-RENEWAL-PAYMENT&status=success')
            ->assertRedirect(route('orders'));

        $this->assertSame($originalExpiry, $license->fresh()->expires_at->timestamp);
        $this->assertSame('awaiting_payment', Order::where('license_id', $license->id)->where('type', 'renewal')->value('status'));
        $this->assertSame(1, License::whereKey($license->id)->count());
    }

    private function dueBkashLicense(): License
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->prices()->create([
            'type' => 'subscription',
            'amount' => 99,
            'currency' => 'BDT',
            'billing_period' => 30,
        ]);
        $purchase = Order::create([
            'order_number' => 'BKASH-ORIGINAL-'.strtoupper(\Illuminate\Support\Str::random(6)),
            'user_id' => $user->id,
            'total_amount' => 99,
            'currency' => 'BDT',
            'status' => 'completed',
            'payment_method' => 'online',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $purchase->id,
            'license_key_hash' => hash('sha256', 'renewal-test-'.$user->id),
            'secret_salt' => 'renewal-test-salt-'.$user->id,
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => now()->subDay(),
            'next_billing_at' => now()->subHour(),
        ]);

        Payment::create([
            'order_id' => $purchase->id,
            'user_id' => $user->id,
            'license_id' => $license->id,
            'gateway' => 'bkash',
            'amount' => 99,
            'status' => 'verified',
            'applied_at' => now(),
        ]);

        return $license;
    }

    private function fakeProvider(bool $completed = false): void
    {
        Http::fake(function ($request) use ($completed) {
            if (str_contains($request->url(), 'token/grant')) {
                return Http::response(['status_code' => '0000', 'id_token' => 'fake-token']);
            }

            if (str_contains($request->url(), '/checkout/create')) {
                return Http::response([
                    'statusCode' => '0000',
                    'paymentID' => 'BKASH-RENEWAL-PAYMENT',
                    'bkashURL' => 'https://checkout.example.com/pay/renewal',
                ]);
            }

            if (str_contains($request->url(), '/checkout/execute')) {
                return Http::response([
                    'paymentID' => 'BKASH-RENEWAL-PAYMENT',
                    'trxID' => 'BKASH-RENEWAL-TRX',
                    'transactionStatus' => $completed ? 'Completed' : 'Initiated',
                    'amount' => '99.00',
                ]);
            }

            if (str_contains($request->url(), '/checkout/payment/status')) {
                return Http::response([
                    'paymentID' => 'BKASH-RENEWAL-PAYMENT',
                    'transactionStatus' => 'Initiated',
                    'amount' => '99.00',
                ]);
            }

            return Http::response([], 404);
        });
    }
}
