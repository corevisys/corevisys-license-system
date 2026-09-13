<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LicenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'order_id' => Order::factory(),
            'license_key_hash' => hash('sha256', Str::random(20)),
            'type' => 'full',
            'status' => 'active',
            'expires_at' => null,
            'bound_domain' => null,
            'bound_ip' => null,
            'bound_fingerprint' => null,
        ];
    }
}
