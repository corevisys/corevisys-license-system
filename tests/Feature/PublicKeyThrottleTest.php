<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PublicKeyThrottleTest extends TestCase
{
    use RefreshDatabase;

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

        RateLimiter::clear('public-key:127.0.0.1');
        Cache::forget('license:public_key');

        config([
            'services.license.signing_public_key' => base64_encode($this->publicKeyPem),
            'services.license.signing_key_id'     => 'test-signing-key-1',
            'services.license.signing_algorithm'  => 'SHA256withRSA',
        ]);
    }

    public function test_public_key_endpoint_is_throttled_at_60_requests_per_minute(): void
    {
        for ($i = 1; $i <= 60; $i++) {
            $response = $this->getJson('/api/v1/license/public-key');
            $this->assertNotSame(429, $response->status(), "Request {$i} should not be throttled");
        }

        // 61st request in the same minute should be rejected with 429 Too Many Requests
        $response61 = $this->getJson('/api/v1/license/public-key');
        $response61->assertStatus(429);
    }
}
