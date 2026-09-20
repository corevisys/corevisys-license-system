<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflinePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_response_contract_fixture_matches_server_envelope(): void
    {
        $fixturePath = file_exists(base_path('tests/Fixtures/license-response-contract.json'))
            ? base_path('tests/Fixtures/license-response-contract.json')
            : base_path('../corevisys-license-package/tests/Fixtures/license-response-contract.json');
        $contract = json_decode(file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame([
            'envelope' => ['success', 'status', 'message', 'data', 'signature', 'key_id', 'algorithm'],
            'data' => ['status', 'license_id', 'product_code', 'license_type', 'expires_at', 'features', 'issued_at', 'offline_valid_until', 'is_grace_period'],
        ], $contract);
    }

    public function test_activate_returns_offline_validation_fields_and_headers()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $privateKeyPem = <<<'PEM'
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

        $publicKeyPem = <<<'PEM'
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

        config()->set('services.license.signing_private_key', base64_encode($privateKeyPem));
        config()->set('services.license.signing_public_key', base64_encode($publicKeyPem));

        $service = new \App\Services\LicenseService();
        $license = $service->createLicense($order, $product);
        $rawKey = $license->raw_key;

        $response = $this->postJson('/api/v1/license/activate', [
            'license_key' => $rawKey,
            'domain' => 'offline.com',
            'ip' => '127.0.0.1',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'status',
                    'license_id',
                    'product_code',
                    'license_type',
                    'expires_at',
                    'features',
                    'offline_valid_until',
                    'issued_at',
                    'is_grace_period',
                ],
                'success',
                'signature',
                'key_id',
                'algorithm'
            ]);

        $response->assertHeader('Cache-Control', 'max-age=3600, private');
    }

    public function test_activate_fails_closed_when_signing_key_is_missing()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        config()->set('services.license.signing_private_key', null);

        $service = new \App\Services\LicenseService();
        $license = $service->createLicense($order, $product);

        $response = $this->postJson('/api/v1/license/activate', [
            'license_key' => $license->raw_key,
            'domain' => 'offline.com',
            'ip' => '127.0.0.1',
        ]);

        $response->assertStatus(503)
            ->assertJsonPath('message', 'License signing is temporarily unavailable.');
    }

    public function test_public_key_endpoint_returns_active_key_metadata()
    {
        config()->set('services.license.signing_key_id', 'current-key');
        config()->set('services.license.signing_algorithm', 'RSA-SHA256');
        config()->set('services.license.signing_public_key', 'Z29vZC1rZXk=');
        config()->set('services.license.signing_public_keys', ['current-key' => 'Z29vZC1rZXk=']);
        config()->set('services.license.rotation_overlap_days', 30);
        config()->set('services.license.signing_revoked_key_ids', []);

        $response = $this->getJson('/api/v1/license/public-key');

        $response->assertStatus(200)
            ->assertJsonPath('key_id', 'current-key')
            ->assertJsonPath('active_key_id', 'current-key')
            ->assertJsonPath('algorithm', 'RSA-SHA256')
            ->assertJsonPath('public_key', 'Z29vZC1rZXk=')
            ->assertJsonPath('rotation_overlap_days', 30);
    }

    public function test_license_history_requires_authentication_and_returns_signed_response()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $service = new \App\Services\LicenseService();
        $license = $service->createLicense($order, $product);

        $unauthenticated = $this->postJson('/api/v1/license/history', [
            'license_key' => $license->raw_key,
        ]);

        $unauthenticated->assertStatus(401);

        $authenticated = $this->actingAs($user)->postJson('/api/v1/license/history', [
            'license_key' => $license->raw_key,
        ]);

        $authenticated->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'license_type',
                    'license_status',
                    'history',
                ],
                'success',
                'signature',
                'key_id',
                'algorithm',
            ]);
    }

    public function test_canonical_payload_is_deterministic()
    {
        $payload = [
            'license_status' => 'active',
            'expires_at' => '2026-12-31T23:59:59Z',
            'protocol_version' => 'v1',
            'client_id' => 'client-123',
            'license_id' => 'lic-01',
        ];

        $canonical = \App\Support\OfflineLicenseVerification::canonicalizePayload($payload);

        $this->assertSame(
            '{"client_id":"client-123","expires_at":"2026-12-31T23:59:59Z","license_id":"lic-01","license_status":"active","protocol_version":"v1"}',
            $canonical
        );
    }

    public function test_old_key_remains_usable_during_rotation_overlap_and_rejected_after_removal()
    {
        $legacyPrivateKeyPem = <<<'PEM'
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

        $legacyPublicKeyPem = <<<'PEM'
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

        $payload = \App\Support\OfflineLicenseVerification::canonicalizePayload([
            'license_id' => 'lic-legacy',
            'license_status' => 'active',
            'license_type' => 'full',
            'expires_at' => '2026-12-31T23:59:59Z',
            'offline_valid_until' => '2026-12-31T23:59:59Z',
            'issued_at' => '2026-09-14T00:00:00Z',
            'key_id' => 'legacy-key',
            'client_id' => 'client-legacy',
            'protocol_version' => 'v1',
        ]);

        openssl_sign($payload, $signature, openssl_get_privatekey($legacyPrivateKeyPem), OPENSSL_ALGO_SHA256);

        config()->set('services.license.signing_public_keys', [
            'legacy-key' => base64_encode($legacyPublicKeyPem),
            'current-key' => base64_encode($legacyPublicKeyPem),
        ]);
        config()->set('services.license.signing_revoked_key_ids', []);

        $this->assertTrue(\App\Support\OfflineLicenseVerification::verifySignature($payload, base64_encode($signature), 'legacy-key'));

        config()->set('services.license.signing_public_keys', [
            'current-key' => base64_encode($legacyPublicKeyPem),
        ]);

        $this->assertFalse(\App\Support\OfflineLicenseVerification::verifySignature($payload, base64_encode($signature), 'legacy-key'));

        config()->set('services.license.signing_public_keys', [
            'legacy-key' => base64_encode($legacyPublicKeyPem),
            'current-key' => base64_encode($legacyPublicKeyPem),
        ]);
        config()->set('services.license.signing_revoked_key_ids', ['legacy-key']);

        $this->assertFalse(\App\Support\OfflineLicenseVerification::verifySignature($payload, base64_encode($signature), 'legacy-key'));
    }

    public function test_legacy_plaintext_only_license_rows_are_rejected()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'LEGACY-PLAIN-KEY'),
            'type' => 'full',
            'status' => 'active',
            'secret_salt' => null,
        ]);

        $service = new \App\Services\LicenseService();
        $result = $service->activate('LEGACY-PLAIN-KEY', 'offline.com', '127.0.0.1');

        $this->assertFalse($result['status']);
        $this->assertEquals('Invalid License Key', $result['message']);
        $this->assertNull($license->fresh()->secret_salt);
    }
}
