<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VersioningTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Seed Settings for strict checking in this test
        SystemSetting::create(['key' => 'api_enabled', 'value' => 'true']);
        SystemSetting::create(['key' => 'min_supported_version', 'value' => '1.0.0']);
    }

    public function test_api_works_with_correct_version()
    {
        $response = $this->withHeaders([
            'X-API-Version' => '1.0.0'
        ])->getJson('/api/v1/products');

        $response->assertStatus(200);
    }

    public function test_api_rejects_older_version()
    {
        $response = $this->withHeaders([
            'X-API-Version' => '0.9.9'
        ])->getJson('/api/v1/products');

        $response->assertStatus(426)
            ->assertJson(['status' => false]);
    }

    public function test_api_rejects_missing_version_if_strict()
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(400)
            ->assertJson(['message' => 'X-API-Version header missing']);
    }

    public function test_kill_switch_activates_maintenance_mode()
    {
        SystemSetting::where('key', 'api_enabled')->update(['value' => 'false']);

        $response = $this->withHeaders([
            'X-API-Version' => '1.0.0'
        ])->getJson('/api/v1/products');

        $response->assertStatus(503)
            ->assertJson(['message' => 'Service Unavailable']);
    }
}
