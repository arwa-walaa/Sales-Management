<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
     use HasFactory;

    protected $fillable = ['name', 'city'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function salesUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('type', 'sales');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
