<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\Order;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\OfflineLicenseVerification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicensePulseTest extends TestCase
{
    use RefreshDatabase;

    private string $privateKeyPem = <<<'PEM'
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA7Fo07jow/Ujnle0yPptVl5EvfVN3SwzwE9h8aYEdDTxOyyaS
zNPxGmu2O8WgwefQ+ZxwNYDwDxHy/KAfargTKDfAdwbdvLj/IcTZMV7fKQj/+5yj
x4SXkiNp8AV6hWmiAoxEHNr18GdX+9mJzodAVy3VD9oxrpo43bpzqm/QJFEmX4aR
Zpfy7d/xvDRU+2nf7llnriJuhIkHKCMXy/gtCVSDAPIxprp1aMp/zf9T5pfVPw4S
YGtDzH5VN/5GWK8s+13//e4KMhr+QepJznz2jA//6Y0Bi1HgqPafWc6bI3jCNGEm
og8rxk271IxAbHdNSfuFGr0zd+jH4vwrArXGywIDAQABAoIBABZnnLHigUdZVF6x
e/xUVEJIaISMV3gdU1rGQFDuBNd+2odGck8JXkcfY8h5vPn0pCotSrO/s8Hx9SM+
eIvwxBwhYNTHqVhc/w5v7xjPgf8NU9rBqALfTlDzm3S9yDYCY/Gy4zgLB5pQ6ZW9
suMJji9VcGeOyvvesbpPFOzYqZXvjr15VT4To3s33mL0JwGQH2/6w5HjFK6liRPq
0UR0/0w5Gzl2ndzoz9ot2AKdWoVu8Tl8S1xmUQGsAciM49uwyi2EJKmUfg7aVZwW
xoC2V9qCVKeUBX2JhKzDaQC5hXWaVew2Mhpg0hJcZb2462vW280ppUadWd7Znnl9
AQfa/MUCgYEA9kpmTmvSbNLYWjMaq95tjcBAwd7QRfZmmozrNBle0QxCUJlpOTwq
VE0xVcuhbmE4kLvGPIRS5qVKXvj4/mKlX3vhz6+iB+h0EPsksPFGD8PdPWdFK83I
aj5VH6Lr/qpm67J4do5lrzCx2ebGIXe8WJ8vFwXq5JqDZBeY+7NJQBcCgYEA9auC
Q588Ddkf9jImvtydVpsKOR6Y8xmcrUh1OARX5CDSg2T3VJ3MCKwaoSQfAjsJKJkO
z5aXgz/tQIpcZaCBrhXg2cLteUzz/GcVRH4n267JUwbGdkLCdsvgXsN+XfTYFbzu
9XzDp8MaXSy7YWmfq7V5MaPjSTtUEcRczAGvi20CgYBi6GgDkFt2JoqKVsGcSfw3
FAEtmlyL7DMyV+tRBetFCqZLFgDi4l2hc0qfyOIwoMyFm1M2FHHyfGjMkTH1fwoo
uWhq7n6krF6IP0Nx58MaK69arHFj8QVOXW/z/4rEwAwLFaY4/mCppWWXO41P/XTf
JjZUCaVWXxLrDGr8kfiVywKBgQCTIZ6ohStgV9NOjYaq9FG+1qfuwaZ0obg2B5k8
bU1+MTIiw0tlgAP8haaFL67qlRTNHa3DIbuoPZcH+lWP/+rqqeu6P4YeCbpuRgZ0
uOGCLlIgyYP+u8jfgQblekuqVcM8caTjnU9IoA6gEvQ+SRX5rnvhAPhUmZWl9mZl
P/U0mQKBgQCmN08XL0jb9Hmvmzh5WHvO1H3FnIHQriH977XMxVBYqEM1tz35UrLR
yuL/pP+qv8bqcs9Q2O2PpxmcWksOWlExG6UTtRFOjAudkEJa1ASZykoshcFuUCcN
PC+67Mt2la81R5XlQ/mlPwYfI1Wz6mlRXPamCiZkFJNc3YCW7cjgOg==
-----END RSA PRIVATE KEY-----
PEM;

    private string $publicKeyPem = <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA7Fo07jow/Ujnle0yPptV
l5EvfVN3SwzwE9h8aYEdDTxOyyaSzNPxGmu2O8WgwefQ+ZxwNYDwDxHy/KAfargT
KDfAdwbdvLj/IcTZMV7fKQj/+5yjx4SXkiNp8AV6hWmiAoxEHNr18GdX+9mJzodA
Vy3VD9oxrpo43bpzqm/QJFEmX4aRZpfy7d/xvDRU+2nf7llnriJuhIkHKCMXy/gt
CVSDAPIxprp1aMp/zf9T5pfVPw4SYGtDzH5VN/5GWK8s+13//e4KMhr+QepJznz2
jA//6Y0Bi1HgqPafWc6bI3jCNGEmog8rxk271IxAbHdNSfuFGr0zd+jH4vwrArXG
ywIDAQAB
-----END PUBLIC KEY-----
PEM;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.license.signing_private_key' => $this->privateKeyPem,
            'services.license.signing_public_key' => $this->publicKeyPem,
            'services.license.signing_key_id' => 'corevisys-key-1',
            'services.license.signing_algorithm' => 'RSA-SHA256',
            'services.license.signing_public_keys' => json_encode([
                'corevisys-key-1' => $this->publicKeyPem,
            ]),
            'license.offline_validity_days' => 7,
            'license.pulse_interval_days' => 30,
            'license.pulse_grace_days' => 7,
        ]);
    }

    private function createLicense(array $overrides = []): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::create([
            'order_number' => 'PULSE-' . uniqid(),
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $key = 'TEST-KEY-' . uniqid();
        $salt = 'salt-' . uniqid();

        $license = License::create(array_merge([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', $key . $salt),
            'secret_salt' => $salt,
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'pulse.test',
            'bound_ip' => '127.0.0.1',
            'bound_fingerprint' => 'fp-pulse',
            'expires_at' => now()->addYear(),
            'activated_at' => now(),
            'last_check_at' => now()->subDay(),
        ], $overrides));

        return [$license, $key];
    }

    public function test_pulse_success_updates_last_check_at_and_creates_no_activation_row(): void
    {
        [$license, $key] = $this->createLicense();
        $oldCheckAt = $license->last_check_at;

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
            'fingerprint' => 'fp-pulse',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'status' => 'active',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'status',
                'data' => [
                    'status',
                    'license_id',
                    'product_code',
                    'license_type',
                    'expires_at',
                    'features',
                    'issued_at',
                    'offline_valid_until',
                    'is_grace_period',
                ],
                'signature',
                'key_id',
                'algorithm',
            ]);

        $license->refresh();
        $this->assertTrue($license->last_check_at->isAfter($oldCheckAt));
        $this->assertEquals(0, LicenseActivation::count());
    }

    public function test_pulse_rejects_invalid_key_with_403(): void
    {
        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => 'NON-EXISTENT-KEY',
            'domain' => 'pulse.test',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'License Inactive/Invalid',
            ]);
    }

    public function test_pulse_rejects_wrong_fingerprint_with_403(): void
    {
        [$license, $key] = $this->createLicense(['bound_fingerprint' => 'correct-fingerprint']);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
            'fingerprint' => 'wrong-fingerprint',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'Environment Fingerprint Required or Mismatched',
            ]);
    }

    public function test_pulse_missing_fingerprint_in_grace_vs_after_deadline(): void
    {
        [$license, $key] = $this->createLicense([
            'bound_fingerprint' => 'bound-fp',
            'fingerprint_missing_grace' => false,
        ]);

        // 1. During grace window (mode strict, missing fingerprint allowed)
        config(['services.license.fingerprint_grace_mode' => true]);
        config(['services.license.fingerprint_enforcement_deadline' => now()->addDays(7)->format('Y-m-d')]);

        $responseGrace = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
            'enforcement_mode' => 'strict',
        ]);

        $responseGrace->assertStatus(200);
        // Pulse must NOT persist fingerprint_missing_grace
        $this->assertFalse((bool) $license->fresh()->fingerprint_missing_grace);

        // 2. After grace deadline (mode strict, missing fingerprint rejected)
        config(['services.license.fingerprint_enforcement_deadline' => now()->subDay()->format('Y-m-d')]);

        $responseAfterDeadline = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
            'enforcement_mode' => 'strict',
        ]);

        $responseAfterDeadline->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'Environment Fingerprint Required or Mismatched',
            ]);
    }

    public function test_pulse_rejects_domain_mismatch_with_403_and_allows_localhost_aliasing(): void
    {
        [$license, $key] = $this->createLicense(['bound_domain' => 'pulse.test']);

        // Domain mismatch
        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'other-domain.test',
            'fingerprint' => 'fp-pulse',
        ]);
        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'Unauthorized Domain',
            ]);

        // Localhost aliasing: bound to localhost, check with 127.0.0.1
        [$localLicense, $localKey] = $this->createLicense([
            'bound_domain' => 'localhost',
            'bound_fingerprint' => null,
        ]);

        $responseAlias1 = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $localKey,
            'domain' => '127.0.0.1',
        ]);
        $responseAlias1->assertStatus(200);

        // Bound to 127.0.0.1, check with localhost
        [$ipLicense, $ipKey] = $this->createLicense([
            'bound_domain' => '127.0.0.1',
            'bound_fingerprint' => null,
        ]);

        $responseAlias2 = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $ipKey,
            'domain' => 'localhost',
        ]);
        $responseAlias2->assertStatus(200);
    }

    public function test_pulse_suspended_license_returns_200_without_offline_valid_until(): void
    {
        [$license, $key] = $this->createLicense([
            'status' => 'suspended',
            'bound_fingerprint' => null,
        ]);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'status' => 'suspended',
                ],
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'status',
                    'issued_at',
                ],
            ]);

        $this->assertNull($response->json('data.offline_valid_until'));
    }

    public function test_pulse_rejects_revoked_license_with_403(): void
    {
        [$license, $key] = $this->createLicense([
            'status' => 'revoked',
            'bound_fingerprint' => null,
        ]);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'License REVOKED',
            ]);
    }

    public function test_pulse_rejects_expired_license_with_no_grace_with_403(): void
    {
        [$license, $key] = $this->createLicense([
            'expires_at' => now()->subDay(),
            'grace_expires_at' => null,
            'bound_fingerprint' => null,
        ]);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'License Expired',
            ]);
    }

    public function test_pulse_allows_expired_license_within_grace_returning_200_with_is_grace_period_true(): void
    {
        [$license, $key] = $this->createLicense([
            'expires_at' => now()->subDay(),
            'grace_expires_at' => now()->addDays(5),
            'bound_fingerprint' => null,
        ]);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'status' => 'active',
                    'is_grace_period' => true,
                ],
            ]);
    }

    public function test_pulse_returns_503_when_signing_key_is_missing(): void
    {
        [$license, $key] = $this->createLicense(['bound_fingerprint' => null]);

        config(['services.license.signing_private_key' => null]);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
        ]);

        $response->assertStatus(503);
    }

    public function test_pulse_returns_503_when_api_enabled_is_false(): void
    {
        SystemSetting::create(['key' => 'api_enabled', 'value' => 'false']);
        [$license, $key] = $this->createLicense(['bound_fingerprint' => null]);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
        ]);

        $response->assertStatus(503)
            ->assertJson(['message' => 'Service Unavailable']);
    }

    public function test_pulse_rejects_outdated_x_api_version_with_426(): void
    {
        SystemSetting::create(['key' => 'min_supported_version', 'value' => '2.0.0']);
        [$license, $key] = $this->createLicense(['bound_fingerprint' => null]);

        $response = $this->withHeaders([
            'X-API-Version' => '1.0.0',
        ])->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
        ]);

        $response->assertStatus(426)
            ->assertJson(['status' => false]);
    }

    public function test_pulse_allows_missing_x_api_version_header(): void
    {
        SystemSetting::create(['key' => 'min_supported_version', 'value' => '1.0.0']);
        [$license, $key] = $this->createLicense(['bound_fingerprint' => null]);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_pulse_offline_valid_until_equals_issued_at_plus_7_days_and_signature_verifies(): void
    {
        [$license, $key] = $this->createLicense(['bound_fingerprint' => null]);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
        ]);

        $response->assertStatus(200);

        $issuedAt = Carbon::parse($response->json('data.issued_at'));
        $offlineValidUntil = Carbon::parse($response->json('data.offline_valid_until'));

        $this->assertEquals(7, $issuedAt->diffInDays($offlineValidUntil));

        $payloadJson = \App\Support\OfflineLicenseVerification::canonicalizePayload($response->json('data'));

        $verified = OfflineLicenseVerification::verifySignature(
            $payloadJson,
            $response->json('signature'),
            $response->json('key_id')
        );

        $this->assertTrue($verified);
    }

    public function test_pulse_rate_limited_429_after_5_requests_per_license_per_hour(): void
    {
        [$license, $key] = $this->createLicense(['bound_fingerprint' => null]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/license/pulse', [
                'license_key' => $key,
                'domain' => 'pulse.test',
            ])->assertStatus(200);
        }

        $sixth = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $key,
            'domain' => 'pulse.test',
        ]);

        $sixth->assertStatus(429)
            ->assertJsonStructure(['message']);
    }

    public function test_flag_stale_command_reports_overdue_licenses_without_changing_status(): void
    {
        // 1. Overdue license (last check 40 days ago)
        [$staleLicense] = $this->createLicense([
            'last_check_at' => now()->subDays(40),
            'status' => 'active',
        ]);

        // 2. Fresh license (last check 5 days ago)
        [$freshLicense] = $this->createLicense([
            'last_check_at' => now()->subDays(5),
            'status' => 'active',
        ]);

        // 3. Brand new license (last check null, but created today)
        [$newLicense] = $this->createLicense([
            'last_check_at' => null,
            'created_at' => now(),
            'status' => 'active',
        ]);

        // 4. Dry run
        $this->artisan('license:flag-stale --dry-run')
            ->assertExitCode(0);

        $this->assertEquals('active', $staleLicense->fresh()->status);
        $this->assertEquals('active', $freshLicense->fresh()->status);
        $this->assertEquals('active', $newLicense->fresh()->status);

        // 5. Normal run: must report but NOT change any statuses in DB
        $this->artisan('license:flag-stale')
            ->assertExitCode(0);

        $this->assertEquals('active', $staleLicense->fresh()->status);
        $this->assertEquals('active', $freshLicense->fresh()->status);
        $this->assertEquals('active', $newLicense->fresh()->status);
    }

    /**
     * CHECK 3 — enforcement_mode governs fingerprint-mismatch outcome on pulse.
     *
     * standard mode: missing fingerprint is allowed even when one is bound
     *                (because shouldEnforceFingerprint returns false).
     * strict mode:   missing fingerprint is rejected when the grace deadline has passed.
     *
     * In BOTH cases pulse must NOT persist fingerprint_missing_grace.
     */
    public function test_pulse_enforcement_mode_changes_fingerprint_mismatch_outcome(): void
    {
        // Put grace deadline in the past so it cannot rescue strict mode.
        config(['services.license.fingerprint_enforcement_deadline' => now()->subDay()->format('Y-m-d')]);
        config(['services.license.fingerprint_grace_mode' => true]);

        // --- standard mode: missing fingerprint accepted ---
        [$standardLicense, $standardKey] = $this->createLicense(['bound_fingerprint' => 'device-fp']);

        $responseStandard = $this->postJson('/api/v1/license/pulse', [
            'license_key'      => $standardKey,
            'domain'           => 'pulse.test',
            // fingerprint intentionally omitted
            'enforcement_mode' => 'standard',
        ]);

        $responseStandard->assertStatus(200)
            ->assertJsonPath('data.status', 'active');

        // Pulse must NOT persist fingerprint_missing_grace (persistGrace=false)
        $this->assertFalse((bool) $standardLicense->fresh()->fingerprint_missing_grace);

        // --- strict mode: missing fingerprint rejected after deadline ---
        [$strictLicense, $strictKey] = $this->createLicense(['bound_fingerprint' => 'device-fp']);

        $responseStrict = $this->postJson('/api/v1/license/pulse', [
            'license_key'      => $strictKey,
            'domain'           => 'pulse.test',
            // fingerprint intentionally omitted
            'enforcement_mode' => 'strict',
        ]);

        $responseStrict->assertStatus(403)
            ->assertJson([
                'status'  => false,
                'message' => 'Environment Fingerprint Required or Mismatched',
            ]);

        // Still must NOT have written fingerprint_missing_grace
        $this->assertFalse((bool) $strictLicense->fresh()->fingerprint_missing_grace);
    }
}

