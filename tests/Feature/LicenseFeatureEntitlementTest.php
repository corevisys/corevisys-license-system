<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseFeatureEntitlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_a_license_feature_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $license = License::factory()->create(['features' => ['reports']]);

        $this->actingAs($admin)
            ->post(route('admin.licenses.features', $license->id), [
                'features' => ['reports', 'exports'],
            ])
            ->assertRedirect();

        $this->assertSame(['reports', 'exports'], $license->fresh()->features);
    }

    public function test_feature_update_requires_admin_authorization(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $license = License::factory()->create();

        $this->actingAs($customer)
            ->post(route('admin.licenses.features', $license->id), [
                'features' => ['reports'],
            ])
            ->assertForbidden();

        $this->assertSame([], $license->fresh()->features);
    }

    public function test_admin_feature_update_rejects_invalid_or_duplicate_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $license = License::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.licenses.show', $license->id))
            ->post(route('admin.licenses.features', $license->id), [
                'features' => ['reports', 'reports', 'contains spaces'],
            ])
            ->assertSessionHasErrors(['features.1', 'features.2']);

        $this->assertSame([], $license->fresh()->features);
    }

    public function test_new_licenses_default_to_an_empty_feature_list(): void
    {
        $license = License::factory()->create();

        $this->assertSame([], $license->fresh()->features);
    }
}
