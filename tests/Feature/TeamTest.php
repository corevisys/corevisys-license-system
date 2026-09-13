<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_can_be_assigned_to_team()
    {
        $user = User::factory()->create();
        $member = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $team = Team::create([
            'name' => 'Support Team',
            'owner_id' => $user->id
        ]);

        $team->users()->attach($user->id, ['role' => 'admin']);
        $team->users()->attach($member->id, ['role' => 'member']);

        $license = License::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'team_id' => $team->id
        ]);

        $this->assertEquals($team->id, $license->team_id);
        $this->assertCount(2, $team->users);
        $this->assertEquals('Support Team', $license->team->name);
    }
}
