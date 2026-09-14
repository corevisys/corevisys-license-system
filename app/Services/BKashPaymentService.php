<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ExchangeRate;
use App\Models\ProductPrice;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BKashPaymentService
{
    protected string $baseUrl;
    protected string $appKey;
    protected string $appSecret;

    public function __construct()
    {
        $this->appKey = (string) config('services.bkash.app_key', '');
        $this->appSecret = (string) config('services.bkash.app_secret', '');
        $this->baseUrl = $this->resolveBaseUrl();
    }

    public function isEnabled(): bool
    {
        return SystemSetting::where('key', 'gateway_bkash_active')->value('value') === '1';
    }

    public function isConfigured(): bool
    {
        return $this->appKey !== '' && $this->appSecret !== '';
    }

    protected function resolveBaseUrl(): string
    {
        $sandbox = (string) SystemSetting::where('key', 'gateway_bkash_sandbox')->value('value');

        return ($sandbox === '0' || strtolower($sandbox) === 'false')
            ? 'https://tokenized.pay.bka.sh/v1.2.0-beta'
            : 'https://tokenized.sandbox.bka.sh/v1.2.0-beta';
    }

    protected function httpClient(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout(30)
            ->connectTimeout(30)
            ->acceptJson();
    }

    protected function resolveCallbackUrl(): string
    {
        $callbackUrl = rtrim((string) config('app.url'), '/') . '/orders/bkash/callback';
        $parsed = parse_url($callbackUrl ?? '');
        $scheme = strtolower((string) ($parsed['scheme'] ?? ''));
        $host = strtolower((string) ($parsed['host'] ?? ''));

        if ($scheme !== 'https' || $host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_contains($host, 'localhost')) {
            throw new \RuntimeException('bKash callback URL must use a public HTTPS domain. Set APP_URL to a public HTTPS URL (for example: https://your-domain.com).');
        }

        return $callbackUrl;
    }

    protected function grantToken(): string
    {
        if (!$this->isConfigured()) {
            throw new \Exception('bKash App Key / App Secret not configured.');
        }

        $response = $this->httpClient()
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->baseUrl . '/token/grant', [
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
            ]);

        if ($response->failed()) {
            Log::error('bKash Token Grant Failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('bKash authentication failed (' . $response->status() . ').');
        }

        $data = $response->json();

        if (($data['status_code'] ?? '') !== '0000' || empty($data['id_token'])) {
            throw new \Exception('bKash token grant rejected: ' . ($data['status_message'] ?? 'Unknown error'));
        }

        return $data['id_token'];
    }

    protected function headers(string $token): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => $token,
            'X-App-Key' => $this->appKey,
        ];
    }

    public function createPayment(Order $order, ProductPrice $price): array
    {
        $amount = $this->amountInBdt($order, $price);
        $callbackUrl = $this->resolveCallbackUrl();

        $token = $this->grantToken();

        $response = $this->httpClient()
            ->withHeaders($this->headers($token))
            ->post($this->baseUrl . '/checkout/create', [
                'mode' => '0011',
                'payerReference' => (string) $order->id,
                'callbackURL' => $callbackUrl,
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $order->order_number,
            ]);

        if ($response->failed()) {
            Log::error('bKash Create Payment Failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('bKash payment creation failed (' . $response->status() . ').');
        }

        $data = $response->json();

        if (($data['statusCode'] ?? '') !== '0000' || empty($data['bkashURL']) || empty($data['paymentID'])) {
            throw new \Exception('bKash payment rejected: ' . ($data['statusMessage'] ?? 'Unknown error'));
        }

        return $data;
    }

    public function executePayment(string $paymentID): array
    {
        $token = $this->grantToken();

        $response = $this->httpClient()
            ->withHeaders($this->headers($token))
            ->post($this->baseUrl . '/checkout/execute', ['paymentID' => $paymentID]);

        if ($response->failed()) {
            Log::error('bKash Execute Payment Failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('bKash payment confirmation failed (' . $response->status() . ').');
        }

        return $response->json();
    }

    public function queryPayment(string $paymentID): array
    {
        $token = $this->grantToken();

        $response = $this->httpClient()
            ->withHeaders($this->headers($token))
            ->post($this->baseUrl . '/checkout/payment/status', ['paymentID' => $paymentID]);

        if ($response->failed()) {
            Log::error('bKash Query Payment Failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception('bKash payment status query failed (' . $response->status() . ').');
        }

        return $response->json();
    }

    /**
     * bKash only settles in BDT. If the order is priced in another currency,
     * convert via the configured exchange rates (rate_to_base = units of USD).
     */
    protected function amountInBdt(Order $order, ProductPrice $price): float
    {
        $amount = (float) $order->total_amount;

        if (strtoupper($order->currency) === 'BDT') {
            return $amount;
        }

        $currencyService = new CurrencyService();
        $inUsd = $currencyService->convertToBase($amount, $order->currency);

        if (!ExchangeRate::where('currency', 'BDT')->exists()) {
            throw new \Exception('BDT exchange rate is not configured.');
        }

        $bdtRate = $currencyService->getRate('BDT');
        if ($bdtRate <= 0) {
            throw new \Exception('BDT exchange rate is not configured.');
        }

        return round($inUsd / $bdtRate, 2);
    }
}
