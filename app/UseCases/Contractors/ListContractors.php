<?php

namespace App\UseCases\Contractors;

use App\Models\Account;
use App\Models\Contractor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListContractors
{
    public function handle(Account $account, array $filters): LengthAwarePaginator
    {
        $query = Contractor::query()->where('account_id', $account->id);

        $query->when(
            $filters['role'] ?? null,
            fn (Builder $builder, string $role) => $builder->where('role', $role),
        );

        $query->when($filters['search'] ?? null, function (Builder $builder, string $search) {
            $builder->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });

        return $query->latest()->paginate(20)->withQueryString();
    }
}

