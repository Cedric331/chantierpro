<?php

namespace App\UseCases\Decisions;

use App\Models\Account;
use App\Models\Decision;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListDecisions
{
    public function handle(Account $account, array $filters): LengthAwarePaginator
    {
        $query = Decision::query()->where('account_id', $account->id);

        $query->when($filters['project'] ?? null, fn (Builder $builder, string $project) => $builder->where('project_id', $project));
        $query->when($filters['search'] ?? null, fn (Builder $builder, string $search) => $builder->where('title', 'like', "%{$search}%"));

        return $query->latest('decided_at')->paginate(12)->withQueryString();
    }
}

