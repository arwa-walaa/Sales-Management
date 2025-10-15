<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BranchController extends Controller
{
    /**
     * GET /api/branches/{branch}/summary
     * Cached for 5 minutes
     */
    public function summary(Request $request, Branch $branch): JsonResponse
    {
        $this->authorize('admin');

        $cacheKey = "branch_summary_{$branch->id}";

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($branch) {
            $baseQuery = Lead::query()->where('branch_id', $branch->id);

            $total = (clone $baseQuery)->count();
            $new = (clone $baseQuery)->where('status', 'new')->count();
            $inProgress = (clone $baseQuery)->where('status', 'in_progress')->count();
            $closed = (clone $baseQuery)->where('status', 'closed')->count();

            $topSales = User::query()
                ->where('users.type', 'sales')
                ->where('users.branch_id', $branch->id)
                ->leftJoin('leads', 'users.id', '=', 'leads.user_id')
                ->selectRaw('users.name as user, COUNT(leads.id) as leads')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('leads')
                ->limit(5)
                ->get();

            return [
                'total_leads' => $total,
                'new' => $new,
                'in_progress' => $inProgress,
                'closed' => $closed,
                'top_sales' => $topSales,
            ];
        });

        return response()->json($data);
    }

    /**
     * POST /api/branches/{branch}/clear-cache
     * Clears cached summary and round-robin pointer for the branch
     */
    public function clearCache(Request $request, Branch $branch): JsonResponse
    {
        $this->authorize('admin');

        Cache::forget("branch_summary_{$branch->id}");

        // Reset round-robin index for this branch
        app(\App\Services\LeadAssignmentService::class)->resetBranchRoundRobin($branch->id);

        return response()->json(['message' => 'Branch cache cleared'], 200);
    }
}
