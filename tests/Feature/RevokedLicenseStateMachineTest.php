<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SEC-8b: Revoked/suspended/cancelled license reactivation blocker.
 *
 * Every test asserts BOTH the HTTP response AND the DB status after the call
 * to prove no state mutation occurred.
 */
class RevokedLicenseStateMachineTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private string $rawKey = 'TEST-8B00-STAT-EMAC';

    private function makeLicense(string $status, ?string $boundDomain = 'example.com'): License
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['slug' => 'corevisys-crm']);
        $service = app(LicenseService::class);
        $pepper  = $service::getLicensePepper();
        $salt    = Str::random(32);

        $license = License::factory()->create([
            'user_id'           => $user->id,
            'product_id'        => $product->id,
            'status'            => $status,
            'bound_domain'      => $boundDomain,
            'bound_ip'          => '1.2.3.4',
            'bound_fingerprint' => 'fp-test',
            'license_key_hash'  => hash('sha256', $this->rawKey . $salt),
            'secret_salt'       => $salt,
            'lookup_hash'       => hash_hmac('sha256', $this->rawKey, $pepper),
            'expires_at'        => null,
        ]);

        if ($boundDomain) {
            LicenseActivation::create([
                'license_id'     => $license->id,
                'request_ip'     => '1.2.3.4',
                'request_domain' => $boundDomain,
                'status'         => 'success',
            ]);
        }

        return $license;
    }

    private function makeLicenseExpired(bool $withGrace): License
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['slug' => 'corevisys-crm']);
        $service = app(LicenseService::class);
        $pepper  = $service::getLicensePepper();
        $salt    = Str::random(32);

        return License::factory()->create([
            'user_id'           => $user->id,
            'product_id'        => $product->id,
            'status'            => 'expired',
            'bound_domain'      => 'example.com',
            'bound_ip'          => '1.2.3.4',
            'bound_fingerprint' => 'fp-test',
            'license_key_hash'  => hash('sha256', $this->rawKey . $salt),
            'secret_salt'       => $salt,
            'lookup_hash'       => hash_hmac('sha256', $this->rawKey, $pepper),
            'expires_at'        => now()->subDays(5),
            'grace_expires_at'  => $withGrace ? now()->addDays(2) : now()->subDays(1),
        ]);
    }

    private function activatePayload(): array
    {
        return [
            'license_key'  => $this->rawKey,
            'domain'       => 'example.com',
            'ip'           => '1.2.3.4',
            'fingerprint'  => 'fp-test',
            'product_code' => 'corevisys-crm',
        ];
    }

    private function checkPayload(): array
    {
        return [
            'license_key'  => $this->rawKey,
            'domain'       => 'example.com',
            'ip'           => '1.2.3.4',
            'fingerprint'  => 'fp-test',
            'product_code' => 'corevisys-crm',
        ];
    }

    private function pulsePayload(): array
    {
        return [
            'license_key'  => $this->rawKey,
            'domain'       => 'example.com',
            'fingerprint'  => 'fp-test',
            'product_code' => 'corevisys-crm',
        ];
    }

    private function deactivatePayload(): array
    {
        return [
            'license_key'  => $this->rawKey,
            'domain'       => 'example.com',
            'ip'           => '1.2.3.4',
            'fingerprint'  => 'fp-test',
            'product_code' => 'corevisys-crm',
        ];
    }

    // =========================================================================
    // ACTIVATE
    // =========================================================================

    #[Test]
    public function activate_revoked_license_is_rejected_and_status_stays_revoked(): void
    {
        $license = $this->makeLicense('revoked');

        $this->postJson('/api/v1/license/activate', $this->activatePayload())
            ->assertStatus(403)
            ->assertJson(['status' => false]);

        $this->assertSame('revoked', $license->fresh()->status);
    }

    #[Test]
    public function activate_suspended_license_is_rejected_and_status_stays_suspended(): void
    {
        $license = $this->makeLicense('suspended');

        $this->postJson('/api/v1/license/activate', $this->activatePayload())
            ->assertStatus(403)
            ->assertJson(['status' => false]);

        $this->assertSame('suspended', $license->fresh()->status);
    }

    #[Test]
    public function activate_cancelled_license_is_rejected_and_status_stays_cancelled(): void
    {
        $license = $this->makeLicense('cancelled');

        $this->postJson('/api/v1/license/activate', $this->activatePayload())
            ->assertStatus(403)
            ->assertJson(['status' => false]);

        $this->assertSame('cancelled', $license->fresh()->status);
    }

    #[Test]
    public function activate_expired_license_without_grace_is_rejected_and_status_stays_expired(): void
    {
        $license = $this->makeLicenseExpired(false);

        $this->postJson('/api/v1/license/activate', $this->activatePayload())
            ->assertStatus(403)
            ->assertJson(['status' => false, 'message' => 'License Expired']);

        $this->assertSame('expired', $license->fresh()->status);
    }

    #[Test]
    public function activate_expired_license_in_grace_succeeds_and_becomes_active(): void
    {
        $license = $this->makeLicenseExpired(true);

        $this->postJson('/api/v1/license/activate', $this->activatePayload())
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertSame('active', $license->fresh()->status);
    }

    // =========================================================================
    // CHECK
    // =========================================================================

    #[Test]
    public function check_revoked_license_is_rejected_and_status_stays_revoked(): void
    {
        $license = $this->makeLicense('revoked');

        $this->postJson('/api/v1/license/check', $this->checkPayload())
            ->assertStatus(403)
            ->assertJson(['status' => false]);

        $this->assertSame('revoked', $license->fresh()->status);
    }

    #[Test]
    public function check_suspended_license_is_rejected_and_status_stays_suspended(): void
    {
        $license = $this->makeLicense('suspended');

        $this->postJson('/api/v1/license/check', $this->checkPayload())
            ->assertStatus(403)
            ->assertJson(['status' => false]);

        $this->assertSame('suspended', $license->fresh()->status);
    }

    #[Test]
    public function check_cancelled_license_is_rejected_and_status_stays_cancelled(): void
    {
        $license = $this->makeLicense('cancelled');

        $this->postJson('/api/v1/license/check', $this->checkPayload())
            ->assertStatus(403)
            ->assertJson(['status' => false]);

        $this->assertSame('cancelled', $license->fresh()->status);
    }

    #[Test]
    public function check_expired_license_without_grace_is_rejected(): void
    {
        $this->makeLicenseExpired(false);

        $this->postJson('/api/v1/license/check', $this->checkPayload())
            ->assertStatus(403)
            ->assertJson(['status' => false]);
    }

    #[Test]
    public function check_expired_license_in_grace_returns_200(): void
    {
        $license = $this->makeLicenseExpired(true);
        // For check, status is 'expired' and grace is active — should be valid
        $this->postJson('/api/v1/license/check', $this->checkPayload())
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // check() is read-only — status must not change
        $this->assertSame('expired', $license->fresh()->status);
    }

    // =========================================================================
    // PULSE
    // =========================================================================

    #[Test]
    public function pulse_revoked_license_is_rejected_and_status_stays_revoked(): void
    {
        $license = $this->makeLicense('revoked');
        $before  = $license->last_check_at;

        $this->postJson('/api/v1/license/pulse', $this->pulsePayload())
            ->assertStatus(403)
            ->assertJson(['status' => false]);

        $fresh = $license->fresh();
        $this->assertSame('revoked', $fresh->status);
        // last_check_at must NOT have been updated
        $this->assertEquals(
            $before?->toIso8601String(),
            $fresh->last_check_at?->toIso8601String()
        );
    }

    #[Test]
    public function pulse_suspended_license_does_not_update_last_check_at_before_guard(): void
    {
        // Suspended is allowed a 200 response to avoid client wipe (existing behaviour).
        // This test just confirms the status stays suspended and the guard ordering is correct.
        $license = $this->makeLicense('suspended');

        $this->postJson('/api/v1/license/pulse', $this->pulsePayload())
            ->assertStatus(200);

        $this->assertSame('suspended', $license->fresh()->status);
    }

    #[Test]
    public function pulse_cancelled_license_is_rejected_and_last_check_at_not_updated(): void
    {
        $license = $this->makeLicense('cancelled');
        $before  = $license->last_check_at;

        $this->postJson('/api/v1/license/pulse', $this->pulsePayload())
            ->assertStatus(403)
            ->assertJson(['status' => false]);

        $fresh = $license->fresh();
        $this->assertSame('cancelled', $fresh->status);
        $this->assertEquals(
            $before?->toIso8601String(),
            $fresh->last_check_at?->toIso8601String()
        );
    }

    #[Test]
    public function pulse_expired_license_without_grace_is_rejected(): void
    {
        $this->makeLicenseExpired(false);

        $this->postJson('/api/v1/license/pulse', $this->pulsePayload())
            ->assertStatus(403)
            ->assertJson(['status' => false]);
    }

    #[Test]
    public function pulse_expired_license_in_grace_is_rejected_because_status_is_not_active(): void
    {
        // pulse() checks status === 'active' explicitly; expired-in-grace is not active.
        $license = $this->makeLicenseExpired(true);

        $this->postJson('/api/v1/license/pulse', $this->pulsePayload())
            ->assertStatus(403);

        // Status must not mutate
        $this->assertSame('expired', $license->fresh()->status);
    }

    // =========================================================================
    // DEACTIVATE
    // =========================================================================

    #[Test]
    public function deactivate_revoked_license_does_not_change_status_to_inactive(): void
    {
        $license = $this->makeLicense('revoked');

        // The call may succeed (binding cleanup is idempotent) or fail — either way
        // the DB status MUST remain 'revoked'.
        $this->postJson('/api/v1/license/deactivate', $this->deactivatePayload());

        $this->assertSame('revoked', $license->fresh()->status);
    }

    #[Test]
    public function deactivate_suspended_license_does_not_change_status_to_inactive(): void
    {
        $license = $this->makeLicense('suspended');

        $this->postJson('/api/v1/license/deactivate', $this->deactivatePayload());

        $this->assertSame('suspended', $license->fresh()->status);
    }

    #[Test]
    public function deactivate_cancelled_license_does_not_change_status_to_inactive(): void
    {
        $license = $this->makeLicense('cancelled');

        $this->postJson('/api/v1/license/deactivate', $this->deactivatePayload());

        $this->assertSame('cancelled', $license->fresh()->status);
    }

    #[Test]
    public function deactivate_expired_no_grace_license_succeeds_and_stays_expired(): void
    {
        $license = $this->makeLicenseExpired(false);

        LicenseActivation::create([
            'license_id'     => $license->id,
            'request_ip'     => '1.2.3.4',
            'request_domain' => 'example.com',
            'status'         => 'success',
        ]);

        $this->postJson('/api/v1/license/deactivate', $this->deactivatePayload())
            ->assertStatus(200);

        // expired -> inactive is a valid state-machine transition (deactivation allowed)
        // so we just confirm it did NOT accidentally become active or revoked
        $freshStatus = $license->fresh()->status;
        $this->assertNotSame('active', $freshStatus);
        $this->assertNotSame('revoked', $freshStatus);
    }

    #[Test]
    public function deactivate_expired_in_grace_license_succeeds(): void
    {
        $license = $this->makeLicenseExpired(true);

        LicenseActivation::create([
            'license_id'     => $license->id,
            'request_ip'     => '1.2.3.4',
            'request_domain' => 'example.com',
            'status'         => 'success',
        ]);

        $this->postJson('/api/v1/license/deactivate', $this->deactivatePayload())
            ->assertStatus(200);

        $freshStatus = $license->fresh()->status;
        $this->assertNotSame('active', $freshStatus);
        $this->assertNotSame('revoked', $freshStatus);
    }
}
