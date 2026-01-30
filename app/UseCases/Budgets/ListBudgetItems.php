<?php

namespace App\UseCases\Budgets;

use App\Models\Account;
use App\Models\ProjectBudgetItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListBudgetItems
{
    /**
     * @return array{items: LengthAwarePaginator, summary: array<string, float>}
     */
    public function handle(Account $account, array $filters): array
    {
        $query = ProjectBudgetItem::query()
            ->where('account_id', $account->id)
            ->when($filters['project'] ?? null, fn ($query, $project) => $query->where('project_id', $project))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', 'like', "%{$category}%"))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"));

        $summary = [
            'estimated' => (float) (clone $query)->sum('estimated_cost'),
            'committed' => (float) (clone $query)->sum('committed_cost'),
            'actual' => (float) (clone $query)->sum('actual_cost'),
            'variation' => (float) (clone $query)->sum('variation_amount'),
        ];

        $items = $query
            ->orderBy('project_id')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return [
            'items' => $items,
            'summary' => $summary,
        ];
    }
}

