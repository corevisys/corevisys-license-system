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

class ResponseLeakSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private License $license;
    private string $validKey = 'VALID-TEST-KEY-SEC1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'name' => 'Security Test Product',
            'slug' => 'sec-product',
            'is_active' => true,
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        $pepper = LicenseService::getLicensePepper();
        $salt = bin2hex(random_bytes(16));
        $this->license = License::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'license_key' => $this->validKey,
            'secret_salt' => $salt,
            'license_key_hash' => hash('sha256', $this->validKey . $salt),
            'lookup_hash' => hash_hmac('sha256', $this->validKey, $pepper),
            'status' => 'active',
            'bound_domain' => 'bound-domain.test',
            'bound_ip' => '127.0.0.1',
            'expires_at' => now()->addYear(),
            'activation_limit' => 5,
        ]);
    }

    #[Test]
    public function test_unknown_key_and_product_mismatch_return_identical_generic_403_across_endpoints(): void
    {
        $expectedBody = [
            'status' => false,
            'message' => 'Invalid License Key',
            'error_code' => 'invalid_license_key',
        ];

        // 1. Activate - Unknown Key
        $activateUnknown = $this->postJson('/api/v1/license/activate', [
            'license_key' => 'UNKNOWN-KEY-999',
            'domain' => 'client.test',
            'ip' => '127.0.0.1',
        ]);

        // 2. Activate - Product Mismatch
        $activateMismatch = $this->postJson('/api/v1/license/activate', [
            'license_key' => $this->validKey,
            'domain' => 'client.test',
            'ip' => '127.0.0.1',
            'product_code' => 'other-unmatched-product',
        ]);

        // 3. Check - Unknown Key
        $checkUnknown = $this->postJson('/api/v1/license/check', [
            'license_key' => 'UNKNOWN-KEY-999',
            'domain' => 'client.test',
            'ip' => '127.0.0.1',
        ]);

        // 4. Check - Product Mismatch
        $checkMismatch = $this->postJson('/api/v1/license/check', [
            'license_key' => $this->validKey,
            'domain' => 'client.test',
            'ip' => '127.0.0.1',
            'product_code' => 'other-unmatched-product',
        ]);

        // 5. Pulse - Unknown Key
        $pulseUnknown = $this->postJson('/api/v1/license/pulse', [
            'license_key' => 'UNKNOWN-KEY-999',
            'domain' => 'client.test',
        ]);

        // 6. Pulse - Product Mismatch
        $pulseMismatch = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $this->validKey,
            'domain' => 'client.test',
            'product_code' => 'other-unmatched-product',
        ]);

        // 7. Deactivate - Unknown Key
        $deactivateUnknown = $this->postJson('/api/v1/license/deactivate', [
            'license_key' => 'UNKNOWN-KEY-999',
            'domain' => 'client.test',
            'ip' => '127.0.0.1',
        ]);

        // 8. Deactivate - Product Mismatch
        $deactivateMismatch = $this->postJson('/api/v1/license/deactivate', [
            'license_key' => $this->validKey,
            'domain' => 'client.test',
            'ip' => '127.0.0.1',
            'product_code' => 'other-unmatched-product',
        ]);

        $responses = [
            'activateUnknown' => $activateUnknown,
            'activateMismatch' => $activateMismatch,
            'checkUnknown' => $checkUnknown,
            'checkMismatch' => $checkMismatch,
            'pulseUnknown' => $pulseUnknown,
            'pulseMismatch' => $pulseMismatch,
            'deactivateUnknown' => $deactivateUnknown,
            'deactivateMismatch' => $deactivateMismatch,
        ];

        foreach ($responses as $name => $response) {
            $this->assertSame(403, $response->getStatusCode(), "Response {$name} status must be 403");
            $this->assertSame($expectedBody, $response->json(), "Response {$name} body must equal expected generic 403 body");
        }

        // Cross-compare all responses: status + body must be equal
        $firstJson = $activateUnknown->json();
        foreach ($responses as $name => $response) {
            $this->assertSame(403, $response->getStatusCode());
            $this->assertSame($firstJson, $response->json(), "Response {$name} body must be identical to activateUnknown");
        }
    }

    #[Test]
    public function test_domain_and_fingerprint_mismatch_do_not_reveal_bound_domain_across_endpoints(): void
    {
        $this->license->update([
            'bound_domain' => 'secret-bound-domain.com',
            'bound_ip' => '1.2.3.4',
            'bound_fingerprint' => 'bound-fp-hash-1234',
            'activated_at' => now(),
            'activation_limit' => 1,
        ]);

        \App\Models\LicenseActivation::create([
            'license_id' => $this->license->id,
            'request_ip' => '1.2.3.4',
            'request_domain' => 'secret-bound-domain.com',
            'status' => 'success',
        ]);

        // 1. Activate - Domain exceeds limit
        $activateDomain = $this->postJson('/api/v1/license/activate', [
            'license_key' => $this->validKey,
            'domain' => 'unauthorised-domain.com',
            'ip' => '5.6.7.8',
            'fingerprint' => 'bound-fp-hash-1234',
        ]);
        $activateDomain->assertStatus(409)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'activation_limit_exceeded')
            ->assertDontSee('secret-bound-domain.com');

        // 2. Check - Domain mismatch
        $checkDomain = $this->postJson('/api/v1/license/check', [
            'license_key' => $this->validKey,
            'domain' => 'unauthorised-domain.com',
            'ip' => '5.6.7.8',
            'fingerprint' => 'bound-fp-hash-1234',
        ]);
        $checkDomain->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'unauthorised_domain')
            ->assertJsonPath('message', 'Unauthorized Domain')
            ->assertDontSee('secret-bound-domain.com');

        // 3. Pulse - Domain mismatch
        $pulseDomain = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $this->validKey,
            'domain' => 'unauthorised-domain.com',
            'fingerprint' => 'bound-fp-hash-1234',
        ]);
        $pulseDomain->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'unauthorised_domain')
            ->assertJsonPath('message', 'Unauthorized Domain')
            ->assertDontSee('secret-bound-domain.com');

        // 4. Activate - Fingerprint mismatch
        $activateFp = $this->postJson('/api/v1/license/activate', [
            'license_key' => $this->validKey,
            'domain' => 'secret-bound-domain.com',
            'ip' => '1.2.3.4',
            'fingerprint' => 'wrong-fp-hash',
        ]);
        $activateFp->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'fingerprint_mismatch')
            ->assertJsonPath('message', 'Environment Fingerprint Mismatch')
            ->assertDontSee('bound-fp-hash-1234');

        // 5. Check - Fingerprint mismatch
        $checkFp = $this->postJson('/api/v1/license/check', [
            'license_key' => $this->validKey,
            'domain' => 'secret-bound-domain.com',
            'ip' => '1.2.3.4',
            'fingerprint' => 'wrong-fp-hash',
        ]);
        $checkFp->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'fingerprint_mismatch')
            ->assertJsonPath('message', 'Environment Fingerprint Mismatch')
            ->assertDontSee('bound-fp-hash-1234');

        // 6. Pulse - Fingerprint mismatch
        $pulseFp = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $this->validKey,
            'domain' => 'secret-bound-domain.com',
            'fingerprint' => 'wrong-fp-hash',
        ]);
        $pulseFp->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'fingerprint_mismatch')
            ->assertJsonPath('message', 'Environment Fingerprint Mismatch')
            ->assertDontSee('bound-fp-hash-1234');
    }

    #[Test]
    public function test_activation_returns_409_when_limit_is_exceeded(): void
    {
        // License with limit 2
        $this->license->update(['activation_limit' => 2, 'bound_domain' => null]);

        // 1st domain
        $res1 = $this->postJson('/api/v1/license/activate', [
            'license_key' => $this->validKey,
            'domain' => 'node1.test',
            'ip' => '1.1.1.1',
        ]);
        $res1->assertStatus(200);

        // 2nd domain
        $res2 = $this->postJson('/api/v1/license/activate', [
            'license_key' => $this->validKey,
            'domain' => 'node2.test',
            'ip' => '1.1.1.2',
        ]);
        $res2->assertStatus(200);

        // 3rd domain: limit reached
        $res3 = $this->postJson('/api/v1/license/activate', [
            'license_key' => $this->validKey,
            'domain' => 'node3.test',
            'ip' => '1.1.1.3',
        ]);

        $res3->assertStatus(409)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'activation_limit_exceeded')
            ->assertJsonPath('message', 'Activation limit reached (2). Please upgrade or reset licenses.');
    }
}
