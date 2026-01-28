<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class Account extends Model
{
    use Billable, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'memberships')
            ->withTimestamps()
            ->withPivot(['role']);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function contractors(): HasMany
    {
        return $this->hasMany(Contractor::class);
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

