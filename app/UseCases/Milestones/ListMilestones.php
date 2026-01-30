<?php

namespace App\UseCases\Milestones;

use App\Models\Account;
use App\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Collection;

class ListMilestones
{
    /**
     * @return Collection<int, ProjectMilestone>
     */
    public function handle(Account $account, array $filters): Collection
    {
        return ProjectMilestone::query()
            ->where('account_id', $account->id)
            ->when($filters['project'] ?? null, fn ($query, $project) => $query->where('project_id', $project))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->orderBy('title')
            ->get();
    }
}



