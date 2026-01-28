<?php

namespace App\Http\Controllers\ProjectTasks;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Project;
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
        $filters = $request->only(['project', 'status', 'search']);

        return Inertia::render('tasks/Index', [
            'filters' => $filters,
            'tasks' => $this->listProjectTasks->handle($account, $filters),
            'projects' => Project::query()->where('account_id', $account->id)->get(['id', 'name']),
            'contractors' => Contractor::query()
                ->where('account_id', $account->id)
                ->get(['id', 'name', 'role']),
        ]);
    }
}

