<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'project_id',
        'name',
        'category',
        'estimated_cost',
        'committed_cost',
        'actual_cost',
        'variation_amount',
        'alerted_at',
        'notes',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'committed_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'variation_amount' => 'decimal:2',
        'alerted_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}



