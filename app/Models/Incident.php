<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'account_id',
        'project_id',
        'title',
        'description',
        'status',
        'impact_days',
        'impact_cost',
        'reported_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'impact_days' => 'integer',
        'impact_cost' => 'decimal:2',
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

