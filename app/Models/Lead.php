<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
class Lead extends Model
{
   use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'status',
        'user_id',
        'branch_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Query Scopes for Filtering
    public function scopeFilterByStatus(Builder $query, ?string $status): Builder
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeFilterByBranch(Builder $query, ?int $branchId): Builder
    {
        if ($branchId) {
            return $query->where('branch_id', $branchId);
        }
        return $query;
    }

    public function scopeFilterByUser(Builder $query, ?int $userId): Builder
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }
        return $query;
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isSales()) {
            return $query->where('user_id', $user->id);
        }
        return $query;
    }
}
