<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LicenseKeyDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:' . base64_encode(str_repeat('t', 32))]);
    }

    public function test_owner_can_reveal_key_that_matches_the_salted_hash(): void
    {
        [$user, $license] = $this->createIssuedLicense();
        Log::spy();

        $response = $this->actingAs($user)->postJson(route('licenses.reveal', $license->id));

        $response->assertOk();
        $key = $response->json('license_key');
        $this->assertSame($license->license_key_hash, hash('sha256', $key . $license->secret_salt));
        $this->assertNotSame($key, DB::table('licenses')->where('id', $license->id)->value('key_encrypted'));
        Log::shouldHaveReceived('info')->once()->with('License key revealed', [
            'license_id' => $license->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_another_user_cannot_reveal_the_license_key(): void
    {
        [, $license] = $this->createIssuedLicense();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->postJson(route('licenses.reveal', $license->id))
            ->assertNotFound();
    }

    public function test_license_configuration_loads_the_key_through_the_owner_scoped_reveal_endpoint(): void
    {
        [$user, $license] = $this->createIssuedLicense();
        $key = $license->raw_key;

        $this->actingAs($user)
            ->get(route('licenses.config', $license->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('LicenseConfig')
                ->where('license.id', $license->id)
                ->missing('license.license_key'))
            ->assertDontSee($key);

        $this->actingAs($user)
            ->postJson(route('licenses.reveal', $license->id))
            ->assertOk()
            ->assertJsonPath('license_key', $key);
    }

    public function test_customer_reveal_returns_the_exact_long_license_key(): void
    {
        [$user, $license] = $this->createIssuedLicense();
        $key = str_repeat('LONG-CUSTOMER-LICENSE-', 30);
        $license->key_encrypted = $key;
        $license->license_key_hash = hash('sha256', $key . $license->secret_salt);
        $license->save();

        $this->actingAs($user)
            ->postJson(route('licenses.reveal', $license->id))
            ->assertOk()
            ->assertJsonPath('license_key', $key);
    }

    public function test_another_user_cannot_open_license_configuration(): void
    {
        [, $license] = $this->createIssuedLicense();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('licenses.config', $license->id))
            ->assertForbidden();
    }

    public function test_admin_license_details_returns_the_complete_key_only_to_admins(): void
    {
        [, $license] = $this->createIssuedLicense();
        $admin = User::factory()->create(['role' => 'admin']);
        $key = $license->raw_key;

        $this->actingAs($admin)
            ->get(route('admin.licenses.show', $license->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/LicenseDetails')
                ->where('license.license_key', $key));
    }

    public function test_admin_license_details_supports_long_keys_and_missing_keys(): void
    {
        [, $license] = $this->createIssuedLicense();
        $admin = User::factory()->create(['role' => 'admin']);
        $key = str_repeat('LONG-LICENSE-KEY-', 30);
        $license->key_encrypted = $key;
        $license->license_key_hash = hash('sha256', $key . $license->secret_salt);
        $license->save();

        $this->actingAs($admin)
            ->get(route('admin.licenses.show', $license->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('license.license_key', $key));

        DB::table('licenses')->where('id', $license->id)->update(['key_encrypted' => null]);

        $this->actingAs($admin)
            ->get(route('admin.licenses.show', $license->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('license.license_key', null));
    }

    public function test_admin_license_details_handles_corrupt_encrypted_key_without_exposing_ciphertext(): void
    {
        [, $license] = $this->createIssuedLicense();
        $admin = User::factory()->create(['role' => 'admin']);
        DB::table('licenses')->where('id', $license->id)->update(['key_encrypted' => 'corrupt-ciphertext']);

        $this->actingAs($admin)
            ->get(route('admin.licenses.show', $license->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('license.license_key', null))
            ->assertDontSee('corrupt-ciphertext')
            ->assertDontSee('DecryptException');
    }

    public function test_non_admin_cannot_view_admin_license_details(): void
    {
        [, $license] = $this->createIssuedLicense();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.licenses.show', $license->id))
            ->assertForbidden();
    }

    public function test_legacy_license_without_encrypted_key_reports_unavailable(): void
    {
        [$user, $license] = $this->createIssuedLicense();
        DB::table('licenses')->where('id', $license->id)->update(['key_encrypted' => null]);

        $this->actingAs($user)
            ->postJson(route('licenses.reveal', $license->id))
            ->assertNotFound()
            ->assertJsonPath('message', 'License key unavailable. Contact support to request a reissue.');
    }

    public function test_corrupt_encrypted_key_reports_unavailable_without_leaking_exception(): void
    {
        [$user, $license] = $this->createIssuedLicense();
        DB::table('licenses')->where('id', $license->id)->update(['key_encrypted' => 'corrupt-ciphertext']);

        $this->actingAs($user)
            ->postJson(route('licenses.reveal', $license->id))
            ->assertNotFound()
            ->assertJsonPath('message', 'License key unavailable. Contact support to request a reissue.')
            ->assertDontSee('DecryptException')
            ->assertDontSee('corrupt-ciphertext');
    }

    public function test_dashboard_and_license_list_do_not_expose_raw_or_encrypted_key(): void
    {
        [$user, $license] = $this->createIssuedLicense();
        $rawKey = $license->raw_key;
        $encryptedKey = DB::table('licenses')->where('id', $license->id)->value('key_encrypted');

        $this->assertArrayNotHasKey('key_encrypted', $license->toArray());

        foreach ([route('dashboard'), route('licenses')] as $url) {
            $response = $this->actingAs($user)->get($url);

            $response->assertOk()
                ->assertDontSee($rawKey)
                ->assertDontSee($encryptedKey)
                ->assertDontSee('key_encrypted');
        }
    }

    private function createIssuedLicense(): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $license = app(LicenseService::class)->createLicense($order, $product);

        return [$user, $license];
    }
}