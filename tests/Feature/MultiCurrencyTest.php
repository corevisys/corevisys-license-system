<?php

namespace Tests\Feature;

use App\Models\ExchangeRate;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_stores_exchange_rate_and_base_amount()
    {
        $user = User::factory()->create();

        // Setup BDT Exchange Rate (Fixed: 1 BDT = 0.0083 USD)
        ExchangeRate::create(['currency' => 'BDT', 'rate_to_base' => 0.0083]);

        $product = Product::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'currency' => 'BDT',
            'total_amount' => 12000, // Roughly 100 USD
            'status' => 'pending'
        ]);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('receipt.pdf', 100);

        $response = $this->actingAs($user)->postJson("/api/v1/orders/{$order->id}/upload-receipt", [
            'receipt' => $file
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => 12000.00,
            'exchange_rate' => 0.0083,
            'base_currency_amount' => 99.60 // 12000 * 0.0083
        ]);
    }
}
