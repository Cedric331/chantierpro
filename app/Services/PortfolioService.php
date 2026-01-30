<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Project;
use Illuminate\Support\Carbon;

class PortfolioService
{
    /**
     * @return array<string, mixed>
     */
    public function overview(Account $account): array
    {
        $deadline = Carbon::now()->addDays(14)->startOfDay();

        $projects = Project::query()
            ->where('account_id', $account->id)
            ->withCount([
                'incidents as open_incidents_count' => fn ($query) => $query->where('status', 'open'),
                'validations as pending_validations_count' => fn ($query) => $query->where('status', 'pending'),
                'milestones as upcoming_milestones_count' => fn ($query) => $query
                    ->where('status', '!=', 'done')
                    ->whereNotNull('due_date')
                    ->where('due_date', '<=', $deadline),
            ])
            ->withSum('budgetItems as estimated_cost_sum', 'estimated_cost')
            ->withSum('budgetItems as committed_cost_sum', 'committed_cost')
            ->withSum('budgetItems as actual_cost_sum', 'actual_cost')
            ->withSum('budgetItems as variation_amount_sum', 'variation_amount')
            ->orderBy('updated_at', 'desc')
            ->get([
                'id',
                'name',
                'client_name',
                'city',
                'status',
                'budget',
                'progress',
                'start_date',
                'end_date',
            ]);

        $projectCount = $projects->count();
        $averageProgress = $projectCount > 0 ? round($projects->avg('progress')) : 0;
        $totalBudget = $projects->sum('budget');
        $totalEstimated = $projects->sum('estimated_cost_sum');
        $totalCommitted = $projects->sum('committed_cost_sum');
        $totalActual = $projects->sum('actual_cost_sum');
        $totalVariation = $projects->sum('variation_amount_sum');
        $delayedProjects = $projects->where('status', 'delayed')->count();

        return [
            'stats' => [
                'projectCount' => $projectCount,
                'averageProgress' => $averageProgress,
                'totalBudget' => $totalBudget,
                'totalEstimated' => $totalEstimated,
                'totalCommitted' => $totalCommitted,
                'totalActual' => $totalActual,
                'totalVariation' => $totalVariation,
                'delayedProjects' => $delayedProjects,
            ],
            'projects' => $projects,
        ];
    }
}



