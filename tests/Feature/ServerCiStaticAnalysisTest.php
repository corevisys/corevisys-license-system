<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerCiStaticAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_ci_workflow_exists_and_contains_no_secrets(): void
    {
        $ciPath = base_path('.github/workflows/ci.yml');
        $this->assertFileExists($ciPath);
        $content = file_get_contents($ciPath);

        $this->assertStringContainsString('composer validate --strict', $content);
        $this->assertStringContainsString('composer audit', $content);
        $this->assertStringContainsString('php artisan test', $content);
        $this->assertStringNotContainsString('secrets.', $content);
        $this->assertStringNotContainsString('BEGIN RSA PRIVATE KEY', $content);
    }

    public function test_phpstan_config_and_baseline_exist(): void
    {
        $this->assertFileExists(base_path('phpstan.neon'));
        $this->assertFileExists(base_path('phpstan-baseline.neon'));

        $config = file_get_contents(base_path('phpstan.neon'));
        $this->assertStringContainsString('level: 5', $config);
        $this->assertStringContainsString('phpstan-baseline.neon', $config);
    }

    public function test_default_min_supported_version_aligns_with_client_v1(): void
    {
        SystemSetting::firstOrCreate(
            ['key' => 'min_supported_version'],
            ['value' => '1.0.0']
        );

        $this->assertSame('1.0.0', SystemSetting::getCached('min_supported_version'));
    }
}
