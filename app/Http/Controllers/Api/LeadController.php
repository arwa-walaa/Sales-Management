<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Resources\LeadCollection;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Jobs\SendLeadAssignmentNotification;
use App\Services\LeadAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    /**
     * GET /api/leads → list leads (filtered by role and optional filters)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Lead::class);

        $status = $request->query('status');
        $branchId = $request->query('branch_id');
        $filterUserId = $request->query('user_id');
        $perPage = (int) ($request->query('per_page', 15));

        $leadsQuery = Lead::query()
            ->with(['user', 'branch'])
            ->forUser($request->user())
            ->filterByStatus($status)
            ->filterByBranch($branchId);

        // Allow admins to filter by arbitrary user_id
        if ($filterUserId && $request->user()->isAdmin()) {
            $leadsQuery->filterByUser((int) $filterUserId);
        }

        $leads = $leadsQuery
            ->orderByDesc('id')
            ->paginate($perPage);

        return LeadResource::collection($leads);
    }

    /**
     * POST /api/leads → create lead (auto-assign sales)
     */
    public function store(StoreLeadRequest $request, LeadAssignmentService $assignmentService): JsonResponse
    {
        
        $this->authorize('create', Lead::class);

        $assignedUser = $assignmentService->assignNextSalesUser((int) $request->branch_id);

        $lead = Lead::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'branch_id' => $request->branch_id,
            'user_id' => $assignedUser?->id,
            'status' => 'new',
        ]);

        // Dispatch queued notification to assigned sales user (if any)
        try {
            SendLeadAssignmentNotification::dispatch($lead->load(['user', 'branch']))->onQueue('default');
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch SendLeadAssignmentNotification', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Lead created successfully',
            'lead' => new LeadResource($lead->load(['user', 'branch'])),
        ], 201);
    }

    /**
     * GET /api/leads/{lead}
     */
    public function show(Lead $lead): LeadResource
    {
        $this->authorize('view', $lead);
        return new LeadResource($lead->load(['user', 'branch']));
    }

    /**
     * PUT /api/leads/{lead} → update lead fields/status
     */
    public function update(UpdateLeadRequest $request, Lead $lead): LeadResource
    {
        $this->authorize('update', $lead);

        $data = $request->only(['name', 'phone', 'status', 'user_id']);

        // Prevent sales users from reassigning leads to others
        if ($request->user()->isSales()) {
            unset($data['user_id']);
        }

        $lead->update($data);

        return new LeadResource($lead->fresh()->load(['user', 'branch']));
    }

    /**
     * DELETE /api/leads/{lead} → soft delete
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $this->authorize('delete', $lead);
        $lead->delete();
        return response()->json(['message' => 'Lead deleted successfully'], 200);
    }
}
