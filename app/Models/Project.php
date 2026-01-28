<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'account_id',
        'name',
        'client_name',
        'address',
        'city',
        'status',
        'budget',
        'start_date',
        'end_date',
        'progress',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'progress' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contractors(): BelongsToMany
    {
        return $this->belongsToMany(Contractor::class, 'project_contractor')
            ->withTimestamps()
            ->withPivot(['role']);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function validations(): HasMany
    {
        return $this->hasMany(Validation::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}

