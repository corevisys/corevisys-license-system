<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Item 2 — Verify bound_domain is present in the signed payload
 * returned by activate, check, and pulse.
 */
class BoundDomainPayloadTest extends TestCase
{
    use RefreshDatabase;

    private License $license;
    private string $key;
    private string $slug;

    protected function setUp(): void
    {
        parent::setUp();

        $pepper = LicenseService::getLicensePepper();
        $salt   = bin2hex(random_bytes(16));
        $this->key = 'BOUND-DOMAIN-KEY-1';

        $user    = User::factory()->create();
        $product = Product::factory()->create(['slug' => 'bd-product', 'is_active' => true]);
        $this->slug = $product->slug;
        $order   = Order::factory()->create(['user_id' => $user->id, 'status' => 'completed']);

        $this->license = License::factory()->create([
            'order_id'         => $order->id,
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'secret_salt'      => $salt,
            'license_key_hash' => hash('sha256', $this->key . $salt),
            'lookup_hash'      => hash_hmac('sha256', $this->key, $pepper),
            'status'           => 'active',
            'bound_domain'     => 'bound.example.com',
            'bound_ip'         => '127.0.0.1',
            'expires_at'       => now()->addYear(),
            'activation_limit' => 3,
        ]);

        $privateKey = <<<'PEM'
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

        config([
            'services.license.signing_private_key' => $privateKey,
            'services.license.signing_key_id'      => 'test-key-1',
            'license.offline_validity_days'         => 7,
        ]);
    }

    private function makeKeyAndLicense(string $keyStr, string $productSlug): License
    {
        $pepper = LicenseService::getLicensePepper();
        $salt   = bin2hex(random_bytes(16));
        $user    = User::factory()->create();
        $product = Product::factory()->create(['slug' => $productSlug, 'is_active' => true]);
        $order   = Order::factory()->create(['user_id' => $user->id, 'status' => 'completed']);

        return License::factory()->create([
            'order_id'         => $order->id,
            'user_id'          => $user->id,
            'product_id'       => $product->id,
            'secret_salt'      => $salt,
            'license_key_hash' => hash('sha256', $keyStr . $salt),
            'lookup_hash'      => hash_hmac('sha256', $keyStr, $pepper),
            'status'           => 'inactive',
            'bound_domain'     => null,
            'expires_at'       => now()->addYear(),
            'activation_limit' => 3,
        ]);
    }

    #[Test]
    public function check_response_includes_bound_domain_in_data(): void
    {
        $response = $this->postJson('/api/v1/license/check', [
            'license_key'  => $this->key,
            'domain'       => 'bound.example.com',
            'ip'           => '1.2.3.4',
            'product_code' => $this->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['bound_domain']]);

        $this->assertSame('bound.example.com', $response->json('data.bound_domain'));
    }

    #[Test]
    public function pulse_response_includes_bound_domain_in_data(): void
    {
        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key'  => $this->key,
            'domain'       => 'bound.example.com',
            'product_code' => $this->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['bound_domain']]);

        $this->assertSame('bound.example.com', $response->json('data.bound_domain'));
    }

    #[Test]
    public function activate_response_includes_bound_domain_after_first_binding(): void
    {
        $newKey = 'BOUND-DOMAIN-KEY-ACT-' . uniqid();
        $license2 = $this->makeKeyAndLicense($newKey, 'bd-product-2');

        $response = $this->postJson('/api/v1/license/activate', [
            'license_key'  => $newKey,
            'domain'       => 'new-activation.test',
            'ip'           => '5.5.5.5',
            'product_code' => $license2->product->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['bound_domain']]);

        // Normalized: no www., lowercase, host-only
        $this->assertSame('new-activation.test', $response->json('data.bound_domain'));
    }

    #[Test]
    public function activate_normalizes_www_and_stores_clean_domain_in_bound_domain(): void
    {
        $newKey   = 'BOUND-DOMAIN-KEY-WWW-' . uniqid();
        $license3 = $this->makeKeyAndLicense($newKey, 'bd-product-3');

        $response = $this->postJson('/api/v1/license/activate', [
            'license_key'  => $newKey,
            'domain'       => 'www.clean-domain.test',
            'ip'           => '6.6.6.6',
            'product_code' => $license3->product->slug,
        ]);

        $response->assertStatus(200);
        // bound_domain in payload is the normalized form (www. stripped)
        $this->assertSame('clean-domain.test', $response->json('data.bound_domain'));
    }
}
