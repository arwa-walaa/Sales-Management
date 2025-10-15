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

        // Should cycle through users: 1,2,3,1,2,3
        $this->assertEquals($assignments[0], $assignments[3]);
        $this->assertEquals($assignments[1], $assignments[4]);
        $this->assertEquals($assignments[2], $assignments[5]);

        $this->assertNotEquals($assignments[0], $assignments[1]);
        $this->assertNotEquals($assignments[1], $assignments[2]);
    }

    public function test_returns_null_when_no_sales_users_exist(): void
    {
        $emptyBranch = Branch::factory()->create();

        $result = $this->service->assignNextSalesUser($emptyBranch->id);

        $this->assertNull($result);
    }

    public function test_reset_round_robin_resets_index(): void
    {
        $users = User::factory()->count(3)->sales()->create([
            'branch_id' => $this->branch->id,
        ]);

        // Assign a few leads
        $this->service->assignNextSalesUser($this->branch->id);
        $this->service->assignNextSalesUser($this->branch->id);

        // Index should be 2
        $this->assertEquals(2, $this->service->getCurrentIndex($this->branch->id));

        // Reset
        $this->service->resetBranchRoundRobin($this->branch->id);

        // Index should be 0
        $this->assertEquals(0, $this->service->getCurrentIndex($this->branch->id));
    }

    public function test_handles_single_sales_user(): void
    {
        $user = User::factory()->sales()->create([
            'branch_id' => $this->branch->id,
        ]);

        $first = $this->service->assignNextSalesUser($this->branch->id);
        $second = $this->service->assignNextSalesUser($this->branch->id);
        $third = $this->service->assignNextSalesUser($this->branch->id);

        // All assignments should be to the same user
        $this->assertEquals($user->id, $first->id);
        $this->assertEquals($user->id, $second->id);
        $this->assertEquals($user->id, $third->id);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
