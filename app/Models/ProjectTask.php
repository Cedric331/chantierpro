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
        'phase_id',
        'title',
        'status',
        'assigned_to',
        'start_date',
        'end_date',
        'duration_days',
        'progress',
        'due_date',
        'requires_photo',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_days' => 'integer',
        'progress' => 'integer',
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

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    public function dependencies()
    {
        return $this->belongsToMany(ProjectTask::class, 'project_task_dependencies', 'task_id', 'depends_on_task_id');
    }

    public function dependents()
    {
        return $this->belongsToMany(ProjectTask::class, 'project_task_dependencies', 'depends_on_task_id', 'task_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

