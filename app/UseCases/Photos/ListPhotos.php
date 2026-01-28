<?php

namespace App\UseCases\Photos;

use App\Models\Account;
use App\Models\Photo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListPhotos
{
    public function handle(Account $account, array $filters): LengthAwarePaginator
    {
        $query = Photo::query()->where('account_id', $account->id);

        $query->when($filters['project'] ?? null, fn (Builder $builder, string $project) => $builder->where('project_id', $project));
        $query->when(
            $filters['task'] ?? null,
            fn (Builder $builder, string $task) => $builder->where('project_task_id', $task),
        );
        $query->when($filters['search'] ?? null, fn (Builder $builder, string $search) => $builder->where('caption', 'like', "%{$search}%"));

        return $query->latest()->paginate(12)->withQueryString();
    }
}

