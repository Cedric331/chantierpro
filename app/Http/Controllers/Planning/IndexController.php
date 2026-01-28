<?php

namespace App\Http\Controllers\Planning;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\ProjectTask;
use App\UseCases\ProjectTasks\ListProjectTasks;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private readonly ListProjectTasks $listProjectTasks)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $filters = $request->only(['project']);

        return Inertia::render('planning/Index', [
            'filters' => $filters,
            'tasks' => ProjectTask::query()
                ->where('account_id', $account->id)
                ->when($filters['project'] ?? null, fn ($query, $project) => $query->where('project_id', $project))
                ->get(['id', 'title', 'status', 'assigned_to', 'due_date', 'project_id']),
            'projects' => Project::query()->where('account_id', $account->id)->get(['id', 'name']),
            'contractors' => Contractor::query()
                ->where('account_id', $account->id)
                ->get(['id', 'name', 'role']),
        ]);
    }
}

