<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasMedia
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
        'title',
        'category',
        'version',
        'status',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile();
    }

    public function getMediaUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl('file');

        return $url !== '' ? $url : null;
    }
}

