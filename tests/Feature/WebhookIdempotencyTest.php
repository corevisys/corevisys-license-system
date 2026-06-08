<?php

namespace Tests\Feature;

use App\Models\ProcessedWebhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_processes_unique_webhook()
    {
        $payload = ['id' => 'evt_test_123', 'type' => 'payment_succeeded'];

        $response = $this->postJson('/api/v1/webhooks/stripe', $payload);

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
        $response = $this->postJson('/api/v1/webhooks/stripe', $payload);

        // Should be 200 OK (idempotent), but message distinct
        $response->assertStatus(200)
            ->assertJson(['message' => 'Webhook already processed']);

        // Ensure only 1 record exists
        $this->assertDatabaseCount('processed_webhooks', 1);
    }
}
