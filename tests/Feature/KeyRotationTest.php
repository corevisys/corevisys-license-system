<?php

// FIX-001 — Key rotation tests
//
// Verifies:
// (A) license:generate-keys command runs and emits expected env-var names.
// (B) .env.example no longer contains the real APP_KEY or real RSA key material.
// (C) A license response signed with an unknown key_id is rejected by OfflineLicenseVerification.
// (D) A license response signed with a revoked key_id is rejected.
// (E) A license response signed with a valid key is accepted.

use App\Support\OfflineLicenseVerification;
use Illuminate\Support\Facades\Config;

// ---------------------------------------------------------------------------
// A — Command emits required env var names
// ---------------------------------------------------------------------------
it('license:generate-keys command outputs required env var names', function () {
    $this->artisan('license:generate-keys --force')
        ->expectsOutputToContain('LICENSE_SIGNING_KEY_ID')
        ->expectsOutputToContain('LICENSE_SIGNING_PRIVATE_KEY')
        ->expectsOutputToContain('LICENSE_SIGNING_PUBLIC_KEY')
        ->expectsOutputToContain('LICENSE_PEPPER')
        ->assertSuccessful();
});

// ---------------------------------------------------------------------------
// B — .env.example must not contain real cryptographic material
// ---------------------------------------------------------------------------
it('.env.example does not contain the old real APP_KEY', function () {
    $content = file_get_contents(base_path('.env.example'));
    expect($content)->not->toContain('Lbj4iCsGffuo1sEsXs31LdRsm5DhZI1QBD7eeqTD3hw=');
});

it('.env.example does not contain real RSA private key material', function () {
    $content = file_get_contents(base_path('.env.example'));
    // The old private key started with this base64 prefix (BEGIN PRIVATE KEY)
    expect($content)->not->toContain('LS0tLS1CRUdJTiBQUklWQVRFIEtFWS0tLS0t');
});

it('.env.example does not contain real RSA public key material', function () {
    $content = file_get_contents(base_path('.env.example'));
    // The old public key started with this base64 prefix (BEGIN PUBLIC KEY)
    expect($content)->not->toContain('LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0K');
});

// ---------------------------------------------------------------------------
// C, D, E — OfflineLicenseVerification::verifySignature() enforcement
// ---------------------------------------------------------------------------

beforeEach(function () {
    $configArgs = [
        'digest_alg'       => 'sha256',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];

    $cnf = env('OPENSSL_CONF') ?: getenv('OPENSSL_CONF');
    if (! ($cnf && file_exists($cnf))) {
        $candidates = [
            'C:\\xampp\\php\\extras\\openssl\\openssl.cnf',
            'C:\\xampp\\apache\\conf\\openssl.cnf',
            'C:\\xampp\\php\\extras\\ssl\\openssl.cnf',
        ];
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                $cnf = $candidate;
                break;
            }
        }
    }

    if ($cnf) {
        $configArgs['config'] = $cnf;
    }

    // Generate a fresh RSA-2048 pair for each test
    $resource = openssl_pkey_new($configArgs);

    if ($resource === false) {
        $this->markTestSkipped('openssl_pkey_new() failed: ' . openssl_error_string());
    }

    if ($cnf) {
        openssl_pkey_export($resource, $this->privateKeyPem, null, ['config' => $cnf]);
    } else {
        openssl_pkey_export($resource, $this->privateKeyPem);
    }
    $details = openssl_pkey_get_details($resource);
    $this->publicKeyPem = $details['key'];
    $this->keyId = 'test-key-' . uniqid();

    // Wire config: keys map is [keyId => base64(pem)]
    Config::set('services.license.signing_public_keys', [
        $this->keyId => base64_encode($this->publicKeyPem),
    ]);
    Config::set('services.license.signing_key_id', $this->keyId);
    Config::set('services.license.signing_revoked_key_ids', []);
});

/**
 * Build a canonicalized payload and its RSA-SHA256 signature.
 */
function buildSignedPayloadParts(string $privateKeyPem, array $data = []): array
{
    $rawData = array_merge([
        'license_key' => 'TEST-KEY',
        'status'      => 'active',
        'expires_at'  => now()->addYear()->toIso8601String(),
        'timestamp'   => now()->toIso8601String(),
    ], $data);

    $canonicalized = OfflineLicenseVerification::canonicalizePayload($rawData);
    openssl_sign($canonicalized, $rawSig, $privateKeyPem, OPENSSL_ALGO_SHA256);

    return [$canonicalized, base64_encode($rawSig)];
}

it('(E) accepts a response signed with the current valid key', function () {
    [$payload, $sig] = buildSignedPayloadParts($this->privateKeyPem);
    expect(OfflineLicenseVerification::verifySignature($payload, $sig, $this->keyId))->toBeTrue();
});

it('(C) rejects a response claiming an unknown key_id', function () {
    [$payload, $sig] = buildSignedPayloadParts($this->privateKeyPem);
    // Correct signature but wrong key_id — key not in the map
    expect(OfflineLicenseVerification::verifySignature($payload, $sig, 'unknown-key-999'))->toBeFalse();
});

it('(D) rejects a response whose key_id is in the revocation list', function () {
    Config::set('services.license.signing_revoked_key_ids', [$this->keyId]);
    [$payload, $sig] = buildSignedPayloadParts($this->privateKeyPem);
    expect(OfflineLicenseVerification::verifySignature($payload, $sig, $this->keyId))->toBeFalse();
});

it('never lists a revoked key id in available_keys regardless of rotation_overlap_days or available_keys', function () {
    $revokedKeyId = 'revoked-key-2026';
    $activeKeyId = 'active-key-2026';

    Config::set('services.license.signing_key_id', $activeKeyId);
    Config::set('services.license.signing_public_key', base64_encode($this->publicKeyPem));
    Config::set('services.license.signing_public_keys', [
        $activeKeyId => base64_encode($this->publicKeyPem),
        $revokedKeyId => base64_encode($this->publicKeyPem),
    ]);
    Config::set('services.license.rotation_overlap_days', 90);
    Config::set('services.license.signing_revoked_key_ids', [$revokedKeyId]);

    \Illuminate\Support\Facades\Cache::forget('license:public_key');

    $meta = OfflineLicenseVerification::buildPublicKeyMetadata();
    $availableKeyIds = array_column($meta['available_keys'], 'key_id');

    expect($availableKeyIds)->toContain($activeKeyId)
        ->and($availableKeyIds)->not->toContain($revokedKeyId);

    $response = $this->getJson('/api/v1/license/public-key');
    $response->assertStatus(200);

    $bodyAvailableIds = array_column($response->json('available_keys'), 'key_id');
    expect($bodyAvailableIds)->toContain($activeKeyId)
        ->and($bodyAvailableIds)->not->toContain($revokedKeyId);
});

it('refuses to serve public-key when active key itself is revoked', function () {
    Config::set('services.license.signing_key_id', $this->keyId);
    Config::set('services.license.signing_public_key', base64_encode($this->publicKeyPem));
    Config::set('services.license.signing_public_keys', [
        $this->keyId => base64_encode($this->publicKeyPem),
    ]);
    Config::set('services.license.rotation_overlap_days', 90);
    Config::set('services.license.signing_revoked_key_ids', [$this->keyId]);

    \Illuminate\Support\Facades\Cache::forget('license:public_key');

    $meta = OfflineLicenseVerification::buildPublicKeyMetadata();
    expect($meta['key_id'])->toBeNull()
        ->and($meta['public_key'])->toBeNull()
        ->and($meta['available_keys'])->toBeEmpty();

    $response = $this->getJson('/api/v1/license/public-key');
    $response->assertStatus(503)
        ->assertJsonPath('message', 'Public key not configured');
});
