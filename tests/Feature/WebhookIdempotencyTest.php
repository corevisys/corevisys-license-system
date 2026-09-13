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

        SystemSetting::create([
            'key' => 'gateway_stripe_webhook_secret',
            'value' => $this->secret,
        ]);
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
}
