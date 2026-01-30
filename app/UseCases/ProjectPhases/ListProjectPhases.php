<?php

namespace App\UseCases\ProjectPhases;

use App\Models\Account;
use App\Models\ProjectPhase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ListProjectPhases
{
    public function handle(Account $account, array $filters = []): Collection
    {
        $query = ProjectPhase::query()->where('account_id', $account->id);

        $query->when($filters['project'] ?? null, fn (Builder $builder, string $project) => $builder->where('project_id', $project));

        return $query->orderBy('position')->get();
    }
}

