<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_reset_is_logged()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $service = new \App\Services\LicenseService();
        $license = $service->createLicense($order, $product);
        $license->update(['bound_domain' => 'old.com']);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/licenses/{$license->id}/reset", [
            'reason' => 'Test reset'
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'license_reset',
            'auditable_type' => License::class,
            'auditable_id' => $license->id,
            'user_id' => $admin->id
        ]);
    }

    public function test_payment_approval_is_logged()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create(['order_id' => $order->id, 'status' => 'pending']);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/payments/{$payment->id}/verify", [
            'action' => 'approve',
            'notes' => 'Verified'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payment_approved',
            'auditable_type' => Payment::class,
            'auditable_id' => $payment->id,
            'user_id' => $admin->id
        ]);

        $log = \App\Models\AuditLog::where('action', 'payment_approved')->first();
        $this->assertEquals('verified', $log->new_values['status']);
    }
}
