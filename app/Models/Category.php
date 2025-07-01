<?php

namespace App\Models;

use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Category extends Model
{
    protected $guarded = [];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
