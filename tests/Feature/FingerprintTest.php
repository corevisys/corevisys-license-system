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

    /**
     * Decision: "enforced from day one."
     * When no FINGERPRINT_GRACE_MODE is set, the config default is false.
     * A missing fingerprint must be rejected without any deadline configuration.
     */
    public function test_fingerprint_grace_mode_default_is_false(): void
    {
        // Unset both config and database — pure default behaviour
        config(['services.license.fingerprint_grace_mode' => config('services.license.fingerprint_grace_mode')]);
        \App\Models\SystemSetting::where('key', 'fingerprint_enforcement_deadline')->delete();
        \Illuminate\Support\Facades\Cache::forget('system_setting_fingerprint_enforcement_deadline');

        // Default must be false (enforced from day one)
        $this->assertFalse(
            config('services.license.fingerprint_grace_mode'),
            'fingerprint_grace_mode config default must be false'
        );

        $service = new LicenseService();
        $this->assertFalse(
            $service->fingerprintGraceWindowIsActive(),
            'Grace window must be inactive when grace_mode=false and no deadline'
        );
    }

    /**
     * Grace window activates only when grace_mode=true AND deadline is in the future.
     */
    public function test_fingerprint_grace_mode_explicit_true_with_future_deadline_activates_grace(): void
    {
        config(['services.license.fingerprint_grace_mode' => true]);
        config(['services.license.fingerprint_enforcement_deadline' => now()->addDays(30)->format('Y-m-d')]);
        \App\Models\SystemSetting::where('key', 'fingerprint_enforcement_deadline')->delete();
        \Illuminate\Support\Facades\Cache::forget('system_setting_fingerprint_enforcement_deadline');

        $service = new LicenseService();
        $this->assertTrue($service->fingerprintGraceWindowIsActive());
    }

    /**
     * Even with grace_mode=true, a past deadline means enforcement is on.
     */
    public function test_fingerprint_grace_mode_explicit_true_with_past_deadline_enforces(): void
    {
        config(['services.license.fingerprint_grace_mode' => true]);
        config(['services.license.fingerprint_enforcement_deadline' => now()->subDay()->format('Y-m-d')]);
        \App\Models\SystemSetting::where('key', 'fingerprint_enforcement_deadline')->delete();
        \Illuminate\Support\Facades\Cache::forget('system_setting_fingerprint_enforcement_deadline');

        $service = new LicenseService();
        $this->assertFalse($service->fingerprintGraceWindowIsActive());
    }

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

    public function test_license_rejects_missing_fingerprint_by_default_in_standard_mode(): void
    {
        // No deadline set in config or database: enforcement is ON by default from day one
        config()->set('services.license.fingerprint_enforcement_deadline', null);
        \App\Models\SystemSetting::where('key', 'fingerprint_enforcement_deadline')->delete();
        \Illuminate\Support\Facades\Cache::forget('system_setting_fingerprint_enforcement_deadline');

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

        // Must reject missing fingerprint by default
        $this->assertFalse($result['status']);
        $this->assertSame('Environment Fingerprint Mismatch', $result['message']);
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

    public function test_fingerprint_grace_window_does_not_roll_forward_when_deadline_is_null(): void
    {
        // Explicitly clear both config and database setting
        config()->set('services.license.fingerprint_grace_mode', true);
        config()->set('services.license.fingerprint_enforcement_deadline', null);
        \App\Models\SystemSetting::where('key', 'fingerprint_enforcement_deadline')->delete();
        \Illuminate\Support\Facades\Cache::forget('system_setting_fingerprint_enforcement_deadline');

        $service = new LicenseService();

        // Must NOT roll forward by adding 90 days; must evaluate to false (inactive grace window)
        $this->assertFalse($service->fingerprintGraceWindowIsActive());

        // Verify enforcement rejects missing fingerprint when strict mode is active and deadline is null
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'FP-NULL-DEADLINE',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => hash('sha256', 'FPKEY_NULL_DL' . 'salt-fpkey-null-dl'),
            'secret_salt' => 'salt-fpkey-null-dl',
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'example.com',
            'bound_ip' => '1.1.1.1',
            'bound_fingerprint' => 'bound_hash',
            'activated_at' => now(),
            'fingerprint_missing_grace' => false,
        ]);

        $result = $service->activate('FPKEY_NULL_DL', 'example.com', '1.1.1.1', null, 'strict');
        $this->assertFalse($result['status']);
        $this->assertSame('Environment Fingerprint Mismatch', $result['message']);
    }

    public function test_fingerprint_grace_window_enforcement_is_cache_safe(): void
    {
        config()->set('services.license.fingerprint_grace_mode', true);
        config()->set('services.license.fingerprint_enforcement_deadline', null);

        // Set deadline in DB and cache
        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'fingerprint_enforcement_deadline'],
            ['value' => now()->addDays(5)->format('Y-m-d')]
        );

        $service = new LicenseService();
        $this->assertTrue($service->fingerprintGraceWindowIsActive());

        // Update DB setting to an expired deadline and ensure cache updates
        \App\Models\SystemSetting::updateOrCreate(
            ['key' => 'fingerprint_enforcement_deadline'],
            ['value' => now()->subDays(5)->format('Y-m-d')]
        );
        $this->assertFalse($service->fingerprintGraceWindowIsActive());
    }

    public function test_fingerprint_enforcement_works_with_config_cache(): void
    {
        // Simulate config:cache by setting static string deadline in config
        $futureDate = now()->addDays(14)->format('Y-m-d');
        config()->set('services.license.fingerprint_enforcement_deadline', $futureDate);
        \App\Models\SystemSetting::where('key', 'fingerprint_enforcement_deadline')->delete();
        \Illuminate\Support\Facades\Cache::forget('system_setting_fingerprint_enforcement_deadline');

        $service = new LicenseService();
        // With future deadline in config, grace is active
        $this->assertTrue($service->fingerprintGraceWindowIsActive());

        // When deadline is unset in cached config, enforcement is ON (grace is inactive)
        config()->set('services.license.fingerprint_enforcement_deadline', null);
        $this->assertFalse($service->fingerprintGraceWindowIsActive());
    }

    public function test_http_fingerprint_enforcement_default_is_enforced(): void
    {
        config()->set('services.license.fingerprint_enforcement_deadline', null);
        \App\Models\SystemSetting::where('key', 'fingerprint_enforcement_deadline')->delete();
        \Illuminate\Support\Facades\Cache::forget('system_setting_fingerprint_enforcement_deadline');

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'FP-HTTP-DEF',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $rawKey = 'FP-KEY-HTTP-DEF';
        $salt = 'salt-def';
        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key' => $rawKey,
            'license_key_hash' => hash('sha256', $rawKey . $salt),
            'lookup_hash' => hash_hmac('sha256', $rawKey, LicenseService::getLicensePepper()),
            'secret_salt' => $salt,
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'example.com',
            'bound_ip' => '1.1.1.1',
            'bound_fingerprint' => 'bound_hash',
            'activated_at' => now(),
        ]);

        // Default: missing fingerprint rejected with 403
        $response = $this->postJson('/api/v1/license/activate', [
            'license_key' => $rawKey,
            'domain' => 'example.com',
            'ip' => '1.1.1.1',
            'product_code' => $product->slug,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'fingerprint_mismatch');
    }

    public function test_http_fingerprint_enforcement_future_deadline_allows_grace(): void
    {
        config()->set('services.license.fingerprint_grace_mode', true);
        config()->set('services.license.fingerprint_enforcement_deadline', now()->addDays(14)->format('Y-m-d'));
        \App\Models\SystemSetting::where('key', 'fingerprint_enforcement_deadline')->delete();
        \Illuminate\Support\Facades\Cache::forget('system_setting_fingerprint_enforcement_deadline');

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'FP-HTTP-FUT',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $rawKey = 'FP-KEY-HTTP-FUT';
        $salt = 'salt-fut';
        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key' => $rawKey,
            'license_key_hash' => hash('sha256', $rawKey . $salt),
            'lookup_hash' => hash_hmac('sha256', $rawKey, LicenseService::getLicensePepper()),
            'secret_salt' => $salt,
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'example.com',
            'bound_ip' => '1.1.1.1',
            'bound_fingerprint' => 'bound_hash',
            'activated_at' => now(),
        ]);

        // Future deadline: missing fingerprint is accepted during grace window
        $response = $this->postJson('/api/v1/license/activate', [
            'license_key' => $rawKey,
            'domain' => 'example.com',
            'ip' => '1.1.1.1',
            'product_code' => $product->slug,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_http_fingerprint_enforcement_past_deadline_is_enforced(): void
    {
        config()->set('services.license.fingerprint_grace_mode', true);
        config()->set('services.license.fingerprint_enforcement_deadline', now()->subDay()->format('Y-m-d'));
        \App\Models\SystemSetting::where('key', 'fingerprint_enforcement_deadline')->delete();
        \Illuminate\Support\Facades\Cache::forget('system_setting_fingerprint_enforcement_deadline');

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = \App\Models\Order::create([
            'order_number' => 'FP-HTTP-PAST',
            'user_id' => $user->id,
            'total_amount' => 10,
            'currency' => 'USD',
            'status' => 'completed',
        ]);

        $rawKey = 'FP-KEY-HTTP-PAST';
        $salt = 'salt-past';
        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key' => $rawKey,
            'license_key_hash' => hash('sha256', $rawKey . $salt),
            'lookup_hash' => hash_hmac('sha256', $rawKey, LicenseService::getLicensePepper()),
            'secret_salt' => $salt,
            'type' => 'full',
            'status' => 'active',
            'bound_domain' => 'example.com',
            'bound_ip' => '1.1.1.1',
            'bound_fingerprint' => 'bound_hash',
            'activated_at' => now(),
        ]);

        // Past deadline: missing fingerprint rejected with 403
        $response = $this->postJson('/api/v1/license/activate', [
            'license_key' => $rawKey,
            'domain' => 'example.com',
            'ip' => '1.1.1.1',
            'product_code' => $product->slug,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'fingerprint_mismatch');
    }
}
