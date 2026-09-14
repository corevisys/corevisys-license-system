<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FingerprintTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_binds_fingerprint_on_first_use()
    {
        // 1. Setup
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'FP-1',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'FPKEY' . 'salt-fpkey'),
            'secret_salt' => 'salt-fpkey',
            'type' => 'full',
            'status' => 'active',
        ]);

        // 2. Activate with Fingerprint
        $service = new LicenseService();
        $result = $service->activate('FPKEY', 'example.com', '1.1.1.1', 'hash_of_environment_xy123');

        // 3. Verify
        $this->assertTrue($result['status']);
        $license->refresh();
        $this->assertEquals('hash_of_environment_xy123', $license->bound_fingerprint);
    }

    public function test_license_rejects_fingerprint_mismatch()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'FP-2',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'FPKEY_MATCH' . 'salt-fpkey-match'),
            'secret_salt' => 'salt-fpkey-match',
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'example.com',
            'bound_ip' => '1.1.1.1',
            'bound_fingerprint' => 'original_fingerprint_hash',
            'activated_at' => now(),
        ]);

        $service = new LicenseService();
        // Same Domain, Different Fingerprint in strict mode
        $result = $service->activate('FPKEY_MATCH', 'example.com', '1.1.1.1', 'modified_fingerprint_hash', 'strict');

        $this->assertFalse($result['status']);
        $this->assertEquals('Environment Fingerprint Mismatch', $result['message']);
    }

    public function test_license_rejects_missing_fingerprint_when_bound()
    {
        config()->set('services.license.fingerprint_grace_mode', false);

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'FP-3',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'FPKEY_OPTIONAL' . 'salt-fpkey-optional'),
            'secret_salt' => 'salt-fpkey-optional',
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'example.com',
            'bound_ip' => '1.1.1.1',
            'bound_fingerprint' => 'original_fingerprint_hash',
            'activated_at' => now(),
        ]);

        $service = new LicenseService();
        $result = $service->activate('FPKEY_OPTIONAL', 'example.com', '1.1.1.1', null, 'strict');

        $this->assertFalse($result['status']);
        $this->assertEquals('Environment Fingerprint Mismatch', $result['message']);
    }

    public function test_license_allows_missing_fingerprint_in_standard_mode()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'FP-4',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed'
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'FPKEY_STANDARD' . 'salt-fpkey-standard'),
            'secret_salt' => 'salt-fpkey-standard',
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'example.com',
            'bound_ip' => '1.1.1.1',
            'bound_fingerprint' => 'original_fingerprint_hash',
            'activated_at' => now(),
        ]);

        $service = new LicenseService();
        $result = $service->activate('FPKEY_STANDARD', 'example.com', '1.1.1.1', null, 'standard');

        $this->assertTrue($result['status']);
    }

    public function test_license_allows_missing_fingerprint_during_grace_window(): void
    {
        config()->set('services.license.fingerprint_grace_mode', true);
        config()->set('services.license.fingerprint_enforcement_deadline', now()->addDays(7)->format('Y-m-d'));

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'FP-5',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'FPKEY_GRACE' . 'salt-fpkey-grace'),
            'secret_salt' => 'salt-fpkey-grace',
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'example.com',
            'bound_ip' => '1.1.1.1',
            'bound_fingerprint' => 'original_fingerprint_hash',
            'activated_at' => now(),
            'fingerprint_missing_grace' => false,
        ]);

        $service = new LicenseService();
        $result = $service->activate('FPKEY_GRACE', 'example.com', '1.1.1.1', null, 'strict');

        $this->assertTrue($result['status']);
        $this->assertTrue($license->fresh()->fingerprint_missing_grace);
    }

    public function test_license_rejects_missing_fingerprint_after_grace_deadline(): void
    {
        config()->set('services.license.fingerprint_grace_mode', true);
        config()->set('services.license.fingerprint_enforcement_deadline', now()->subDay()->format('Y-m-d'));

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'FP-6',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'FPKEY_DEADLINE' . 'salt-fpkey-deadline'),
            'secret_salt' => 'salt-fpkey-deadline',
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'example.com',
            'bound_ip' => '1.1.1.1',
            'bound_fingerprint' => 'original_fingerprint_hash',
            'activated_at' => now(),
            'fingerprint_missing_grace' => false,
        ]);

        $service = new LicenseService();
        $result = $service->activate('FPKEY_DEADLINE', 'example.com', '1.1.1.1', null, 'strict');

        $this->assertFalse($result['status']);
        $this->assertSame('Environment Fingerprint Mismatch', $result['message']);
        $this->assertFalse($license->fresh()->fingerprint_missing_grace);
    }
}
