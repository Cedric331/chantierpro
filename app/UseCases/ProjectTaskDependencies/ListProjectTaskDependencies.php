<?php

namespace App\UseCases\ProjectTaskDependencies;

use App\Models\Account;
use App\Models\ProjectTaskDependency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ListProjectTaskDependencies
{
    public function handle(Account $account, array $filters = []): Collection
    {
        $query = ProjectTaskDependency::query()->where('account_id', $account->id);

        $query->when($filters['project'] ?? null, fn (Builder $builder, string $project) => $builder->where('project_id', $project));

        return $query->get();
    }
}

