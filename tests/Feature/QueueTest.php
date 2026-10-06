<?php

namespace Tests\Feature;

use App\Jobs\ProcessLicenseRenewal;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_renewal_command_dispatches_jobs()
    {
        Queue::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        License::factory()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'auto_renew' => true,
            'status' => 'active',
            'next_billing_at' => Carbon::now()->subMinute()
        ]);

        $this->artisan('license:renew-subscriptions')->assertExitCode(0);

        Queue::assertPushed(ProcessLicenseRenewal::class, 1);
    }
}
