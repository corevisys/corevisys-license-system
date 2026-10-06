<?php

namespace Tests\Feature;

use App\Models\ProcessedWebhook;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected string $secret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.webhook_secret' => $this->secret]);
    }

    protected function signedRequest(array $data): array
    {
        // Build a real, verifiable Stripe-Signature header for the payload.
        $payload = json_encode($data);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $this->secret);

        return [$payload, 't=' . $timestamp . ',v1=' . $signature];
    }

    public function test_processes_unique_webhook()
    {
        $payload = ['id' => 'evt_test_123', 'type' => 'payment_succeeded'];

        [$rawPayload, $sigHeader] = $this->signedRequest($payload);

        $response = $this->call('POST', '/api/v1/webhooks/stripe', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => $sigHeader,
        ], $rawPayload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Processed']);

        $this->assertDatabaseHas('processed_webhooks', [
            'gateway' => 'stripe',
            'event_id' => 'evt_test_123'
        ]);
    }

    public function test_rejects_duplicate_webhook_as_success()
    {
        // 1. First Request
        ProcessedWebhook::create([
            'gateway' => 'stripe',
            'event_id' => 'evt_duplicate_999',
            'payload' => []
        ]);

        // 2. Duplicate Request
        $payload = ['id' => 'evt_duplicate_999', 'type' => 'retry'];

        [$rawPayload, $sigHeader] = $this->signedRequest($payload);

        $response = $this->call('POST', '/api/v1/webhooks/stripe', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => $sigHeader,
        ], $rawPayload);

        // Should be 200 OK (idempotent), but message distinct
        $response->assertStatus(200)
            ->assertJson(['message' => 'Already Processed']);

        // Ensure only 1 record exists
        $this->assertDatabaseCount('processed_webhooks', 1);
    }

    public function test_invoice_payment_failed_webhook_starts_grace_and_marks_payment_failed(): void
    {
        $user = \App\Models\User::factory()->create();
        $product = \App\Models\Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'STRIPE-FAIL-1',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        \App\Models\Payment::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'gateway' => 'stripe',
            'transaction_id' => 'pi_failed_test',
            'amount' => 10,
            'status' => 'pending',
            'gateway_response' => [],
        ]);

        $license = \App\Models\License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'STRIPEFAIL' . 'salt-stripefail'),
            'secret_salt' => 'salt-stripefail',
            'type' => 'subscription',
            'status' => 'active',
            'auto_renew' => true,
            'expires_at' => now()->addDays(10),
            'next_billing_at' => now()->addDays(10),
            'gateway_subscription_id' => 'sub_123',
        ]);

        $payload = [
            'id' => 'evt_inv_failed_123',
            'type' => 'invoice.payment_failed',
            'data' => ['object' => [
                'id' => 'in_failed_123',
                'subscription' => 'sub_123',
                'payment_intent' => 'pi_failed_test',
            ]],
        ];

        [$rawPayload, $sigHeader] = $this->signedRequest($payload);

        $response = $this->call('POST', '/api/v1/webhooks/stripe', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => $sigHeader,
        ], $rawPayload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Processed']);

        $license->refresh();
        $this->assertNotNull($license->grace_expires_at);
        $this->assertTrue($license->grace_expires_at->isFuture());
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'transaction_id' => 'pi_failed_test', 'status' => 'failed']);
    }
}
