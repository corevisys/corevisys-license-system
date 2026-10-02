<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossProductLicenseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $cheapProduct;
    private Product $expensiveProduct;
    private License $license;
    private string $licenseKey = 'CHEAP-PRODUCT-KEY-123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->cheapProduct = Product::factory()->create([
            'name' => 'Cheap CRM',
            'slug' => 'cheap-crm',
            'is_active' => true,
        ]);

        $this->expensiveProduct = Product::factory()->create([
            'name' => 'Enterprise ERP',
            'slug' => 'expensive-erp',
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
            'product_id' => $this->cheapProduct->id,
            'license_key' => $this->licenseKey,
            'secret_salt' => $salt,
            'license_key_hash' => hash('sha256', $this->licenseKey . $salt),
            'lookup_hash' => hash_hmac('sha256', $this->licenseKey, $pepper),
            'status' => 'active',
            'expires_at' => now()->addYear(),
            'activation_limit' => 5,
        ]);
    }

    public function test_activation_rejects_mismatched_product_code(): void
    {
        $response = $this->postJson('/api/v1/license/activate', [
            'license_key' => $this->licenseKey,
            'domain' => 'client-app.test',
            'ip' => '127.0.0.1',
            'product_code' => 'expensive-erp', // Mismatch!
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'invalid_license_key');
    }

    public function test_activation_accepts_matching_product_code(): void
    {
        $response = $this->postJson('/api/v1/license/activate', [
            'license_key' => $this->licenseKey,
            'domain' => 'client-app.test',
            'ip' => '127.0.0.1',
            'product_code' => 'cheap-crm', // Matches!
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.product_code', 'cheap-crm');
    }

    public function test_check_rejects_mismatched_product_code(): void
    {
        $this->license->update([
            'bound_domain' => 'client-app.test',
            'bound_ip' => '127.0.0.1',
            'activated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'license_key' => $this->licenseKey,
            'domain' => 'client-app.test',
            'ip' => '127.0.0.1',
            'product_code' => 'expensive-erp', // Mismatch!
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'invalid_license_key');
    }

    public function test_check_accepts_matching_product_code(): void
    {
        $this->license->update([
            'bound_domain' => 'client-app.test',
            'bound_ip' => '127.0.0.1',
            'activated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/license/check', [
            'license_key' => $this->licenseKey,
            'domain' => 'client-app.test',
            'ip' => '127.0.0.1',
            'product_code' => 'cheap-crm', // Matches!
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_pulse_rejects_mismatched_product_code(): void
    {
        $this->license->update([
            'bound_domain' => 'client-app.test',
            'bound_ip' => '127.0.0.1',
            'activated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $this->licenseKey,
            'domain' => 'client-app.test',
            'product_code' => 'expensive-erp', // Mismatch!
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error_code', 'invalid_license_key');
    }

    public function test_pulse_accepts_matching_product_code(): void
    {
        $this->license->update([
            'bound_domain' => 'client-app.test',
            'bound_ip' => '127.0.0.1',
            'activated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/license/pulse', [
            'license_key' => $this->licenseKey,
            'domain' => 'client-app.test',
            'product_code' => 'cheap-crm', // Matches!
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }
}
