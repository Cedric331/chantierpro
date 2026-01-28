<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'project_id',
        'title',
        'status',
        'assigned_to',
        'due_date',
        'requires_photo',
    ];

    protected $casts = [
        'due_date' => 'date',
        'requires_photo' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

