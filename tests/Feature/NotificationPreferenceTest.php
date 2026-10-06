<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_and_update_preferences()
    {
        $user = User::factory()->create();

        // Fetch
        $response = $this->actingAs($user)->getJson('/api/v1/notifications/preferences');
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Update
        $response = $this->actingAs($user)->postJson('/api/v1/notifications/preferences', [
            'notify_via_sms' => true,
            'phone_number' => '1234567890'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'notify_via_sms' => true,
                    'phone_number' => '1234567890'
                ]
            ]);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'notify_via_sms' => true,
            'phone_number' => '1234567890'
        ]);
    }
}
