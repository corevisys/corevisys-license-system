<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairMissingLicensesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:' . base64_encode(str_repeat('t', 32))]);
    }

    public function test_default_run_is_dry_and_reports_candidates_and_skips(): void
    {
        [$eligible] = $this->makeOrder('verified', true);
        [$unverified] = $this->makeOrder('pending', true);
        [$missingItem] = $this->makeOrder('verified', false);

        $this->artisan('license:repair-missing')
            ->expectsOutputToContain("Candidate order={$eligible->id}")
            ->expectsOutputToContain("Skipped order={$unverified->id} reason=payment not verified")
            ->expectsOutputToContain("Skipped order={$missingItem->id} reason=no item")
            ->assertExitCode(0);

        $this->assertDatabaseCount('licenses', 0);
        $this->assertDatabaseHas('orders', ['id' => $eligible->id, 'status' => 'completed']);
    }

    public function test_force_repairs_only_eligible_orders_and_never_duplicates_existing_license(): void
    {
        [$eligible, $user, $product] = $this->makeOrder('verified', true);
        [$unverified] = $this->makeOrder('pending', true);
        [$existingLicenseOrder, $existingUser, $existingProduct] = $this->makeOrder('verified', true);
        app(LicenseService::class)->createLicense($existingLicenseOrder, $existingProduct);

        $this->artisan('license:repair-missing', ['--force' => true])
            ->expectsOutputToContain("Fulfilled order={$eligible->id}")
            ->assertExitCode(0);

        $this->assertDatabaseHas('licenses', [
            'order_id' => $eligible->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
        $this->assertDatabaseHas('orders', ['id' => $unverified->id, 'status' => 'completed']);
        $this->assertDatabaseCount('licenses', 2);
        $this->assertSame(1, $existingLicenseOrder->licenses()->count());
    }

    private function makeOrder(string $paymentStatus, bool $withItem): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        Payment::factory()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'status' => $paymentStatus,
            'gateway' => 'manual',
        ]);

        if ($withItem) {
            $order->items()->create([
                'product_id' => $product->id,
                'price' => 100,
                'license_type' => 'full',
            ]);
        }

        return [$order, $user, $product];
    }
}