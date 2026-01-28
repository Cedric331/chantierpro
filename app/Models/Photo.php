<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\ProjectTask;

class Photo extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $appends = ['media_url'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'account_id',
        'project_id',
        'project_task_id',
        'caption',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function getMediaUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('image');

        return $url !== '' ? $url : null;
    }
}

