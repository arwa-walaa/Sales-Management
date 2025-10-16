<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\User;
use App\Services\LeadAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RoundRobinAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected LeadAssignmentService $service;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LeadAssignmentService();
        $this->branch = Branch::factory()->create();
    }

    public function test_assigns_leads_in_round_robin_order(): void
    {
        // Create 3 sales users
        $users = User::factory()->count(3)->sales()->create([
            'branch_id' => $this->branch->id,
        ]);

        $assignments = [];

        // Assign 6 leads and check round-robin pattern
        for ($i = 0; $i < 6; $i++) {
            $assignedUser = $this->service->assignNextSalesUser($this->branch->id);
            $assignments[] = $assignedUser->id;
        }

      
        $this->assertEquals($assignments[0], $assignments[3]);
        $this->assertEquals($assignments[1], $assignments[4]);
        $this->assertEquals($assignments[2], $assignments[5]);

        $this->assertNotEquals($assignments[0], $assignments[1]);
        $this->assertNotEquals($assignments[1], $assignments[2]);
    }

   

  

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
