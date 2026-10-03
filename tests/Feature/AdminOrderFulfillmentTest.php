<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AdminOrderFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:' . base64_encode(str_repeat('t', 32))]);
    }

    public function test_admin_confirmation_fulfills_an_order_once(): void
    {
        [$admin, $order, $payment, $product] = $this->makeOrder('pending', 'pending');

        $this->actingAs($admin)->post(route('admin.orders.verify', $order->id));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'verified',
            'verified_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('licenses', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'product_id' => $product->id,
        ]);
        $this->assertDatabaseCount('licenses', 1);

        $this->actingAs($admin)->post(route('admin.orders.verify', $order->id));

        $this->assertDatabaseCount('licenses', 1);
        $result = app(\App\Services\OrderFulfillmentService::class)->fulfillOrder($order->fresh());
        $this->assertSame($order->licenses()->first()->id, $result['license']->id);
    }

    public function test_admin_confirmation_repairs_completed_order_without_license(): void
    {
        [$admin, $order, $payment] = $this->makeOrder('completed', 'verified');

        $this->actingAs($admin)->post(route('admin.orders.verify', $order->id));

        $this->assertDatabaseCount('licenses', 1);
        $this->assertDatabaseHas('licenses', ['order_id' => $order->id]);
        $this->assertSame($admin->id, $payment->fresh()->verified_by);
    }

    public function test_completed_order_without_item_is_skipped_without_changing_state(): void
    {
        [, $order, $payment] = $this->makeOrder('completed', 'verified', false);
        $updatedAt = $order->fresh()->updated_at;
        Log::spy();

        $result = app(\App\Services\OrderFulfillmentService::class)->fulfillOrder($order);

        $this->assertNull($result);
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertEquals($updatedAt, $order->fresh()->updated_at);
        $this->assertSame('verified', $payment->fresh()->status);
        $this->assertDatabaseCount('licenses', 0);
        Log::shouldHaveReceived('warning')->once()->with(
            'OrderFulfillment: Completed order has no fulfillable item. Skipping repair.',
            ['order_id' => $order->id]
        );
    }

    public function test_order_without_an_item_fails_without_changing_order_or_payment(): void
    {
        [$admin, $order, $payment] = $this->makeOrder('pending', 'pending', false);

        $response = $this->actingAs($admin)->from(route('admin.orders'))->post(route('admin.orders.verify', $order->id));

        $response->assertRedirect(route('admin.orders'))
            ->assertSessionHas('error')
            ->assertSessionHasErrors('fulfillment');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
        $this->assertDatabaseCount('licenses', 0);
    }

    public function test_non_admin_cannot_confirm_an_order(): void
    {
        [, $order] = $this->makeOrder('pending', 'pending');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.orders.verify', $order->id))->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseCount('licenses', 0);
    }

    public function test_fulfillment_exception_is_rethrown_not_swallowed(): void
    {
        // An order with no items (pending) has no OrderItem, so the transaction
        // closure throws RuntimeException — the catch block must re-throw it.
        [, $order] = $this->makeOrder('pending', 'pending', false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/has no items to fulfill/');

        app(\App\Services\OrderFulfillmentService::class)->fulfillOrder($order);
    }

    private function makeOrder(string $status, string $paymentStatus, bool $withItem = true): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => $status]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'status' => $paymentStatus,
        ]);

        if ($withItem) {
            $order->items()->create([
                'product_id' => $product->id,
                'price' => 100,
                'license_type' => 'full',
            ]);
        }

        return [$admin, $order, $payment, $product];
    }
}