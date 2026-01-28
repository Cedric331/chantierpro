<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProjectService
{
    public function list(Account $account, array $filters): LengthAwarePaginator
    {
        $query = Project::query()->where('account_id', $account->id);

        $query->when($filters['status'] ?? null, fn (Builder $builder, string $status) => $builder->where('status', $status));
        $query->when($filters['city'] ?? null, fn (Builder $builder, string $city) => $builder->where('city', $city));
        $query->when($filters['client'] ?? null, fn (Builder $builder, string $client) => $builder->where('client_name', 'like', "%{$client}%"));
        $query->when($filters['search'] ?? null, function (Builder $builder, string $search) {
            $builder->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        });

        return $query->latest()->paginate(12)->withQueryString();
    }
}

