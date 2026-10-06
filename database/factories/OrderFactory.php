<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . Str::random(10),
            'user_id' => User::factory(),
            'total_amount' => 100.00,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'stripe',
        ];
    }
}
