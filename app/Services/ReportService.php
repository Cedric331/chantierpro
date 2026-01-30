<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Decision;
use App\Models\Incident;
use App\Models\Photo;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\Validation;
use Carbon\Carbon;

class ReportService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Account $account, array $filters): array
    {
        $projectId = $filters['project'] ?? null;
        $from = ($filters['from'] ?? null) ? Carbon::parse($filters['from'])->startOfDay() : null;
        $to = ($filters['to'] ?? null) ? Carbon::parse($filters['to'])->endOfDay() : null;

        $applyFilters = function ($query) use ($account, $projectId, $from, $to) {
            $query->where('account_id', $account->id);

            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            if ($from) {
                $query->where('created_at', '>=', $from);
            }

            if ($to) {
                $query->where('created_at', '<=', $to);
            }

            return $query;
        };

        $incidentsQuery = $applyFilters(Incident::query());
        $validationsQuery = $applyFilters(Validation::query());
        $decisionsQuery = $applyFilters(Decision::query());
        $photosQuery = $applyFilters(Photo::query());
        $tasksQuery = $applyFilters(ProjectTask::query());
        $milestonesQuery = $applyFilters(ProjectMilestone::query());

        return [
            'summary' => [
                'incidents' => $incidentsQuery->count(),
                'validations' => $validationsQuery->count(),
                'decisions' => $decisionsQuery->count(),
                'photos' => $photosQuery->count(),
                'tasks' => $tasksQuery->count(),
                'milestones' => $milestonesQuery->count(),
            ],
            'incidents' => $incidentsQuery
                ->latest()
                ->take(10)
                ->get(['id', 'title', 'status', 'created_at', 'project_id']),
            'validations' => $validationsQuery
                ->latest()
                ->take(10)
                ->get(['id', 'title', 'status', 'created_at', 'project_id']),
            'decisions' => $decisionsQuery
                ->latest('decided_at')
                ->take(10)
                ->get(['id', 'title', 'decided_at', 'project_id']),
            'photos' => $photosQuery
                ->latest()
                ->take(10)
                ->selectRaw('id, caption as title, created_at, project_id')
                ->get(),
            'tasks' => $tasksQuery
                ->latest()
                ->take(10)
                ->get(['id', 'title', 'status', 'due_date', 'project_id']),
            'milestones' => $milestonesQuery
                ->latest()
                ->take(10)
                ->get(['id', 'title', 'status', 'due_date', 'project_id']),
            'filters' => [
                'project' => $projectId,
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
        ];
    }
}

