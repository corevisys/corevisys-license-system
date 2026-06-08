<?php

namespace Tests\Feature;

use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_valid_transitions()
    {
        $license = License::factory()->create(['status' => 'inactive']);
        $machine = new LicenseStateMachine();

        // inactive -> active
        $machine->transition($license, 'active');
        $this->assertEquals('active', $license->fresh()->status);

        // active -> expired
        $machine->transition($license, 'expired');
        $this->assertEquals('expired', $license->fresh()->status);

        // expired -> active
        $machine->transition($license, 'active');
        $this->assertEquals('active', $license->fresh()->status);
    }

    public function test_blocks_invalid_transitions()
    {
        $license = License::factory()->create(['status' => 'suspended']);
        $machine = new LicenseStateMachine();

        $this->expectException(\Exception::class);

        // suspended -> inactive (Not allowed in our map)
        $machine->transition($license, 'inactive');
    }
}
