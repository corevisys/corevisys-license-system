<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfflinePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_receipt()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('receipt.pdf', 100);

        $response = $this->actingAs($user)->postJson("/api/v1/orders/{$order->id}/upload-receipt", [
            'receipt' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Receipt uploaded. Waiting for admin approval.']);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'pending',
            'gateway' => 'offline'
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'awaiting_payment'
        ]);
    }

    public function test_admin_can_approve_offline_payment()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'awaiting_payment']);

        // Simulating Order Item
        $order->items()->create([
            'product_id' => $product->id,
            'price' => 100,
            'license_type' => 'full'
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'pending',
            'gateway' => 'offline'
        ]);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/payments/{$payment->id}/verify", [
            'action' => 'approve',
            'notes' => 'Looks good'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Payment approved and license generated']);

        $this->assertEquals('verified', $payment->fresh()->status);
        $this->assertEquals('completed', $order->fresh()->status);
        // License should be generated
        $this->assertDatabaseHas('licenses', [
            'order_id' => $order->id,
            'status' => 'inactive' // LicenseService defaults to inactive upon creation
        ]);
    }
}
