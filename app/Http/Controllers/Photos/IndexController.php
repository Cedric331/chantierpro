<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use App\UseCases\Photos\ListPhotos;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private readonly ListPhotos $listPhotos)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $filters = $request->only(['project', 'task', 'search']);

        return Inertia::render('photos/Index', [
            'filters' => $filters,
            'photos' => $this->listPhotos->handle($account, $filters),
            'projects' => Project::query()->where('account_id', $account->id)->get(['id', 'name']),
            'tasks' => ProjectTask::query()
                ->where('account_id', $account->id)
                ->get(['id', 'title', 'project_id']),
        ]);
    }
}

