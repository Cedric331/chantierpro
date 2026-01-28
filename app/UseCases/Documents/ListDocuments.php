<?php

namespace App\UseCases\Documents;

use App\Models\Account;
use App\Models\Document;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListDocuments
{
    public function handle(Account $account, array $filters): LengthAwarePaginator
    {
        $query = Document::query()->where('account_id', $account->id);

        $query->when($filters['project'] ?? null, fn (Builder $builder, string $project) => $builder->where('project_id', $project));
        $query->when($filters['category'] ?? null, fn (Builder $builder, string $category) => $builder->where('category', $category));
        $query->when($filters['status'] ?? null, fn (Builder $builder, string $status) => $builder->where('status', $status));
        $query->when($filters['search'] ?? null, fn (Builder $builder, string $search) => $builder->where('title', 'like', "%{$search}%"));

        return $query->latest()->paginate(12)->withQueryString();
    }
}

