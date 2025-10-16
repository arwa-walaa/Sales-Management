<?php
namespace App\Services;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class LeadAssignmentService
{
    /**
     * Assign a lead to the next available sales user in the branch using round-robin.
     *
     * @param int $branchId
     * @return User|null
     */
    public function assignNextSalesUser(int $branchId): ?User
    {
        $branch = Branch::findOrFail($branchId);

        // Get all sales users for this branch, ordered by ID for consistency
        $salesUsers = $branch->salesUsers()->orderBy('id')->get();

        if ($salesUsers->isEmpty()) {
            return null;
        }

        // Cache key for tracking round-robin position
        $cacheKey = "branch_{$branchId}_round_robin_index";

        // Get current index from cache (default to 0)
        $currentIndex = Cache::get($cacheKey, 0);

        // Get the user at current index
        $assignedUser = $salesUsers[$currentIndex];

        // Calculate next index (circular)
        $nextIndex = ($currentIndex + 1) % $salesUsers->count();

        // Store next index in cache (no expiration, persistent)
        Cache::forever($cacheKey, $nextIndex);

        return $assignedUser;
    }

    /**
     * Reset round-robin counter for a branch (useful for testing or admin operations).
     *
     * @param int $branchId
     * @return void
     */
    public function resetBranchRoundRobin(int $branchId): void
    {
        $cacheKey = "branch_{$branchId}_round_robin_index";
        Cache::forget($cacheKey);
    }

  
 
}
