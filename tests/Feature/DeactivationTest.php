<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * FIX-004: Deactivation endpoint tests.
 *
 * Covers:
 *  - Successful deactivation from the bound domain
 *  - Successful deactivation from a secondary (activation-history) domain
 *  - Rejection when the requesting domain has no activation history
 *  - Rejection when the submitted fingerprint does not match the bound fingerprint
 *  - 404 for an invalid license key
 *  - 422 for a product-code mismatch
 *  - License becomes re-activatable after a successful deactivation
 */
class DeactivationTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeActiveLicense(
        string $domain      = 'example.com',
        string $fingerprint = 'fp-abc123',
        string $productSlug = 'corevisys-crm',
    ): License {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['slug' => $productSlug]);

        $service = app(LicenseService::class);
        $pepper  = $service::getLicensePepper();
        $key     = 'TEST-ABCD-EFGH-IJKL';
        $salt    = \Illuminate\Support\Str::random(32);

        $license = License::factory()->create([
            'user_id'           => $user->id,
            'product_id'        => $product->id,
            'status'            => 'active',
            'bound_domain'      => $domain,
            'bound_ip'          => '1.2.3.4',
            'bound_fingerprint' => $fingerprint,
            'license_key_hash'  => hash('sha256', $key . $salt),
            'secret_salt'       => $salt,
            'lookup_hash'       => hash_hmac('sha256', $key, $pepper),
        ]);

        // Seed one successful activation row for the domain
        LicenseActivation::create([
            'license_id'     => $license->id,
            'request_ip'     => '1.2.3.4',
            'request_domain' => $domain,
            'status'         => 'success',
        ]);

        return $license;
    }

    private function deactivatePayload(
        string  $key,
        string  $domain      = 'example.com',
        ?string $fingerprint = 'fp-abc123',
        ?string $productCode = 'corevisys-crm',
    ): array {
        return array_filter([
            'license_key'  => $key,
            'domain'       => $domain,
            'ip'           => '1.2.3.4',
            'fingerprint'  => $fingerprint,
            'product_code' => $productCode,
        ], fn ($v) => $v !== null);
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    #[Test]
    public function deactivation_succeeds_from_bound_domain(): void
    {
        $license = $this->makeActiveLicense('example.com', 'fp-abc123');
        $rawKey  = app(LicenseService::class)->rotateLicenseKey(
            $license, 'TEST-ABCD-EFGH-IJKL'
        )->raw_key;

        $response = $this->postJson('/api/v1/license/deactivate', [
            'license_key'  => $rawKey,
            'domain'       => 'example.com',
            'ip'           => '1.2.3.4',
            'fingerprint'  => 'fp-abc123',
            'product_code' => 'corevisys-crm',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        // Binding cleared
        $license->refresh();
        $this->assertNull($license->bound_domain);
        $this->assertNull($license->bound_fingerprint);

        // Activation row marked deactivated
        $this->assertDatabaseHas('license_activations', [
            'license_id'     => $license->id,
            'request_domain' => 'example.com',
            'failure_reason' => 'Deactivated by client',
        ]);
    }

    #[Test]
    public function deactivation_is_rejected_for_unauthorised_domain(): void
    {
        $license = $this->makeActiveLicense('example.com', 'fp-abc123');
        $rawKey  = app(LicenseService::class)->rotateLicenseKey(
            $license, 'TEST-ABCD-EFGH-IJKL'
        )->raw_key;

        $response = $this->postJson('/api/v1/license/deactivate', [
            'license_key'  => $rawKey,
            'domain'       => 'rogue.com',     // no activation history
            'ip'           => '9.9.9.9',
            'fingerprint'  => 'fp-abc123',
            'product_code' => 'corevisys-crm',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error_code' => 'unauthorised_domain']);

        // License untouched
        $this->assertDatabaseHas('licenses', [
            'id'           => $license->id,
            'bound_domain' => 'example.com',
        ]);
    }

    #[Test]
    public function deactivation_is_rejected_on_fingerprint_mismatch(): void
    {
        $license = $this->makeActiveLicense('example.com', 'fp-CORRECT');
        $rawKey  = app(LicenseService::class)->rotateLicenseKey(
            $license, 'TEST-ABCD-EFGH-IJKL'
        )->raw_key;

        $response = $this->postJson('/api/v1/license/deactivate', [
            'license_key'  => $rawKey,
            'domain'       => 'example.com',
            'ip'           => '1.2.3.4',
            'fingerprint'  => 'fp-WRONG',      // mismatch
            'product_code' => 'corevisys-crm',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error_code' => 'fingerprint_mismatch']);

        // Binding untouched
        $license->refresh();
        $this->assertSame('example.com', $license->bound_domain);
    }

    #[Test]
    public function deactivation_returns_404_for_invalid_key(): void
    {
        $this->postJson('/api/v1/license/deactivate', [
            'license_key'  => 'INVALID-KEY-0000',
            'domain'       => 'example.com',
            'ip'           => '1.2.3.4',
        ])->assertStatus(404);
    }

    #[Test]
    public function deactivation_returns_422_for_product_code_mismatch(): void
    {
        $license = $this->makeActiveLicense('example.com', 'fp-abc123', 'corevisys-crm');
        $rawKey  = app(LicenseService::class)->rotateLicenseKey(
            $license, 'TEST-ABCD-EFGH-IJKL'
        )->raw_key;

        $this->postJson('/api/v1/license/deactivate', [
            'license_key'  => $rawKey,
            'domain'       => 'example.com',
            'ip'           => '1.2.3.4',
            'fingerprint'  => 'fp-abc123',
            'product_code' => 'wrong-product',
        ])->assertStatus(422)->assertJson(['error_code' => 'product_code_mismatch']);
    }

    #[Test]
    public function license_is_reactivatable_after_successful_deactivation(): void
    {
        $license = $this->makeActiveLicense('example.com', 'fp-abc123');
        $rawKey  = app(LicenseService::class)->rotateLicenseKey(
            $license, 'TEST-ABCD-EFGH-IJKL'
        )->raw_key;

        // Deactivate
        $this->postJson('/api/v1/license/deactivate', [
            'license_key'  => $rawKey,
            'domain'       => 'example.com',
            'ip'           => '1.2.3.4',
            'fingerprint'  => 'fp-abc123',
            'product_code' => 'corevisys-crm',
        ])->assertOk();

        // Re-activate on the same domain should succeed
        $response = $this->postJson('/api/v1/license/activate', [
            'license_key'  => $rawKey,
            'domain'       => 'example.com',
            'ip'           => '1.2.3.4',
            'fingerprint'  => 'fp-abc123',
            'product_code' => 'corevisys-crm',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
    }

    #[Test]
    public function deactivation_succeeds_from_secondary_domain_with_activation_history(): void
    {
        $license = $this->makeActiveLicense('primary.com', 'fp-primary');
        $rawKey  = app(LicenseService::class)->rotateLicenseKey(
            $license, 'TEST-ABCD-EFGH-IJKL'
        )->raw_key;

        // Seed a second successful activation for a secondary domain
        LicenseActivation::create([
            'license_id'     => $license->id,
            'request_ip'     => '5.5.5.5',
            'request_domain' => 'secondary.com',
            'status'         => 'success',
        ]);

        // Deactivate from the secondary domain (no fingerprint bound to secondary)
        $response = $this->postJson('/api/v1/license/deactivate', [
            'license_key'  => $rawKey,
            'domain'       => 'secondary.com',
            'ip'           => '5.5.5.5',
            'product_code' => 'corevisys-crm',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        // Primary binding remains (secondary is not the primary)
        $license->refresh();
        $this->assertSame('primary.com', $license->bound_domain);
    }
}
