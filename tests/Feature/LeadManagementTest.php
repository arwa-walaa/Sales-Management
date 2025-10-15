<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $salesUser;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::factory()->create();

        $this->admin = User::factory()->admin()->create();

        $this->salesUser = User::factory()->sales()->create([
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_admin_can_view_all_leads(): void
    {
        Lead::factory()->count(5)->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/leads');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_sales_user_can_only_view_own_leads(): void
    {
        Lead::factory()->count(3)->create([
            'user_id' => $this->salesUser->id,
            'branch_id' => $this->branch->id,
        ]);

        Lead::factory()->count(2)->create(['branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->getJson('/api/leads');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_lead_is_created_and_auto_assigned(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/leads', [
                'name' => 'John Doe',
                'phone' => '1234567890',
                'branch_id' => $this->branch->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'lead' => [
                    'id',
                    'name',
                    'phone',
                    'status',
                    'user_id',
                    'branch_id',
                ],
            ]);

        $this->assertDatabaseHas('leads', [
            'name' => 'John Doe',
            'phone' => '1234567890',
            'user_id' => $this->salesUser->id,
        ]);
    }

    public function test_sales_user_can_update_own_lead(): void
    {
        $lead = Lead::factory()->create([
            'user_id' => $this->salesUser->id,
            'branch_id' => $this->branch->id,
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->putJson("/api/leads/{$lead->id}", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_sales_user_cannot_update_others_lead(): void
    {
        $otherUser = User::factory()->sales()->create(['branch_id' => $this->branch->id]);

        $lead = Lead::factory()->create([
            'user_id' => $otherUser->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->putJson("/api/leads/{$lead->id}", [
                'status' => 'closed',
            ]);

        $response->assertStatus(403);
    }

    public function test_lead_can_be_soft_deleted(): void
    {
        $lead = Lead::factory()->create([
            'user_id' => $this->salesUser->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->deleteJson("/api/leads/{$lead->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    }

    public function test_leads_can_be_filtered_by_status(): void
    {
        Lead::factory()->count(2)->create([
            'status' => 'new',
            'user_id' => $this->salesUser->id,
            'branch_id' => $this->branch->id,
        ]);

        Lead::factory()->count(3)->create([
            'status' => 'closed',
            'user_id' => $this->salesUser->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->getJson('/api/leads?status=new');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
