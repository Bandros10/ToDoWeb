<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use SoftDeletes;
    protected $guarded = [];

     public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Method untuk memfilter task
    public function scopeFilter($query, array $filters)
    {
        if ($filters['status'] ?? false) {
            $query->where('is_completed', $filters['status'] === 'completed');
        }
        if ($filters['priority'] ?? false) {
            $query->where('priority', $filters['priority']);
        }
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
