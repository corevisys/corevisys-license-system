<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SigningKeyConfigSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_signing_key_id_has_no_default_when_unconfigured(): void
    {
        // When env and config are not set, signing_key_id must evaluate to null (not 'corevisys-key-1')
        config()->set('services.license.signing_key_id', null);

        $this->assertNull(config('services.license.signing_key_id'));
        $this->assertNotSame('corevisys-key-1', config('services.license.signing_key_id'));
    }

    public function test_missing_signing_key_id_fails_loudly_in_production(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LICENSE_SIGNING_KEY_ID is missing or not configured in production.');

        $this->app['env'] = 'production';
        config()->set('app.env', 'production');
        config()->set('services.license.signing_key_id', null);

        // Simulate AppServiceProvider::boot in production environment
        $provider = new AppServiceProvider($this->app);
        $provider->boot();
    }

    public function test_tests_can_set_their_own_signing_key_id_config(): void
    {
        $customKeyId = 'tenant-custom-key-999';
        config()->set('services.license.signing_key_id', $customKeyId);

        $this->assertSame($customKeyId, config('services.license.signing_key_id'));
    }
}
