<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'gateway' => 'stripe',
            'transaction_id' => 'tx_' . Str::random(10),
            'amount' => 100.00,
            'status' => 'verified',
            'gateway_response' => [],
        ];
    }
}
