<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Decision;
use App\Models\Incident;
use App\Models\Project;
use App\Models\Validation;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(Account $account): array
    {
        $activeProjects = Project::query()
            ->where('account_id', $account->id)
            ->whereIn('status', ['preparation', 'in_progress', 'delayed'])
            ->count();

        $delayedProjects = Project::query()
            ->where('account_id', $account->id)
            ->where('status', 'delayed')
            ->count();

        $totalBudget = Project::query()
            ->where('account_id', $account->id)
            ->sum('budget');

        $pendingValidations = Validation::query()
            ->where('account_id', $account->id)
            ->where('status', 'pending')
            ->count();

        $openIncidents = Incident::query()
            ->where('account_id', $account->id)
            ->where('status', 'open')
            ->count();

        $recentProjects = Project::query()
            ->where('account_id', $account->id)
            ->latest()
            ->take(4)
            ->get();

        $urgentValidations = Validation::query()
            ->where('account_id', $account->id)
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentDecisions = Decision::query()
            ->where('account_id', $account->id)
            ->latest('decided_at')
            ->take(6)
            ->get();

        $projects = Project::query()
            ->where('account_id', $account->id)
            ->get(['id', 'name', 'progress', 'budget', 'status']);

        $averageProgress = $projects->count() > 0
            ? round($projects->avg('progress'))
            : 0;

        $budgetConsumed = $projects->sum(fn (Project $project) => round($project->budget * ($project->progress / 100)));
        $budgetRemaining = max($totalBudget - $budgetConsumed, 0);

        $statusBreakdown = $projects
            ->groupBy('status')
            ->map(fn ($items) => $items->count())
            ->toArray();

        return [
            'stats' => [
                'activeProjects' => $activeProjects,
                'delayedProjects' => $delayedProjects,
                'totalBudget' => $totalBudget,
                'pendingValidations' => $pendingValidations,
                'openIncidents' => $openIncidents,
            ],
            'charts' => [
                'averageProgress' => $averageProgress,
                'budget' => [
                    'consumed' => $budgetConsumed,
                    'remaining' => $budgetRemaining,
                ],
                'progressByProject' => [
                    'labels' => $projects->pluck('name')->values(),
                    'series' => $projects->pluck('progress')->map(fn ($value) => (int) $value)->values(),
                ],
                'statusBreakdown' => $statusBreakdown,
            ],
            'recentProjects' => $recentProjects,
            'urgentValidations' => $urgentValidations,
            'recentDecisions' => $recentDecisions,
        ];
    }
}

