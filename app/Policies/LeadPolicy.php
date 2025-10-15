<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class LeadPolicy
{
    /**
     * Determine if the user can view any leads
     */
    public function viewAny(User $user): bool
    {
        return true; // Both admin and sales can view leads
    }

    /**
     * Determine if the user can view the lead
     */
    public function view(User $user, Lead $lead): bool
    {
        // Admin can view all leads
        if ($user->isAdmin()) {
            return true;
        }

        // Sales can only view their own leads
        return $user->id === $lead->user_id;
    }

    /**
     * Determine if the user can create leads
     */
    public function create(User $user): bool
    {
        return true; // Both admin and sales can create leads
    }

    /**
     * Determine if the user can update the lead
     */
    public function update(User $user, Lead $lead): bool
    {
        // Admin can update all leads
        if ($user->isAdmin()) {
            return true;
        }

        // Sales can only update their own leads
        return $user->id === $lead->user_id;
    }

    /**
     * Determine if the user can delete the lead
     */
    public function delete(User $user, Lead $lead): bool
    {
        // Admin can delete all leads
        if ($user->isAdmin()) {
            return true;
        }

        // Sales can only delete their own leads
        return $user->id === $lead->user_id;
    }

    /**
     * Determine if the user can restore the lead
     */
    public function restore(User $user, Lead $lead): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the lead
     */
    public function forceDelete(User $user, Lead $lead): bool
    {
        return $user->isAdmin();
    }
}
