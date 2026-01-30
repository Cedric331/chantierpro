<?php

namespace App\Http\Controllers\Budgets;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\UsageTrackingService;
use App\UseCases\Budgets\ListBudgetItems;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(
        private readonly ListBudgetItems $listBudgetItems,
        private readonly UsageTrackingService $usageTrackingService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $this->usageTrackingService->track($account, 'budgets');
        $filters = $request->only(['project', 'category', 'search']);
        $projectSearch = $request->string('project_search')->toString();

        $result = $this->listBudgetItems->handle($account, $filters);
        $projects = Project::query()
            ->where('account_id', $account->id)
            ->when($projectSearch, fn ($query) => $query->where('name', 'like', "%{$projectSearch}%"))
            ->orderBy('name')
            ->paginate(20, ['id', 'name', 'budget'], 'project_page')
            ->withQueryString();

        $selectedProject = null;
        if (! empty($filters['project'])) {
            $selectedProject = Project::query()
                ->where('account_id', $account->id)
                ->where('id', $filters['project'])
                ->first(['id', 'name', 'budget']);
        }

        return Inertia::render('budgets/Index', [
            'filters' => $filters,
            'projectSearch' => $projectSearch,
            'summary' => $result['summary'],
            'items' => $result['items'],
            'projects' => $projects,
            'selectedProject' => $selectedProject,
        ]);
    }
}



