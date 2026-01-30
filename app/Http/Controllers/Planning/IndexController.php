<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Services\UsageTrackingService;
use App\UseCases\ProjectPhases\ListProjectPhases;
use App\UseCases\ProjectTaskDependencies\ListProjectTaskDependencies;
use App\UseCases\Milestones\ListMilestones;
use App\UseCases\ProjectTasks\ListProjectTasks;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(
        private readonly ListProjectTasks $listProjectTasks,
        private readonly ListMilestones $listMilestones,
        private readonly ListProjectPhases $listProjectPhases,
        private readonly ListProjectTaskDependencies $listProjectTaskDependencies,
        private readonly UsageTrackingService $usageTrackingService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $this->usageTrackingService->track($account, 'planning');
        $filters = $request->only(['project']);

        return Inertia::render('planning/Index', [
            'filters' => $filters,
            'tasks' => ProjectTask::query()
                ->where('account_id', $account->id)
                ->when($filters['project'] ?? null, fn ($query, $project) => $query->where('project_id', $project))
                ->get([
                    'id',
                    'title',
                    'status',
                    'assigned_to',
                    'start_date',
                    'end_date',
                    'duration_days',
                    'progress',
                    'due_date',
                    'project_id',
                    'phase_id',
                ]),
            'milestones' => $this->listMilestones->handle($account, [
                'project' => $filters['project'] ?? null,
            ])->map->only(['id', 'title', 'status', 'due_date', 'project_id', 'owner_name', 'description']),
            'phases' => $this->listProjectPhases->handle($account, [
                'project' => $filters['project'] ?? null,
            ])->map->only(['id', 'title', 'description', 'start_date', 'end_date', 'position', 'project_id']),
            'dependencies' => $this->listProjectTaskDependencies->handle($account, [
                'project' => $filters['project'] ?? null,
            ])->map->only(['id', 'task_id', 'depends_on_task_id', 'dependency_type', 'project_id']),
            'projects' => Project::query()
                ->where('account_id', $account->id)
                ->orderBy('name')
                ->get(['id', 'name', 'status']),
            'contractors' => Contractor::query()
                ->where('account_id', $account->id)
                ->get(['id', 'name', 'role']),
        ]);
    }
}

