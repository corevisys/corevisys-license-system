<?php

namespace Database\Seeders;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LicenseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
            'password' => bcrypt('password')
        ]);

        $product = Product::firstOrCreate(
            ['slug' => 'corevisys-pro'],
            [
                'name' => 'Corevisys Pro',
                'description' => 'Professional Enterprise License',
                'type' => 'software',
                'status' => 'published',
            ]
        );

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'user_id' => $user->id,
            'status' => 'completed',
            'total_amount' => 999.00,
            'payment_status' => 'paid',
            'currency' => 'USD'
        ]);

        // Generate a consistently 'known' key for valid testing, or random
        // Let's make one known key
        $key = 'CORE-TEST-KEY-2026';
        $salt = Str::random(32);

        License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key' => $key,
            'license_key_hash' => hash('sha256', $key . $salt), // Correct hash with salt
            'secret_salt' => $salt,
            'type' => 'full',
            'status' => 'active',
            'enforcement_mode' => 'active',
            'activation_limit' => 5,
            'expires_at' => now()->addYear(),
        ]);
        
        $this->command->info("License Created: $key");
    }
}
