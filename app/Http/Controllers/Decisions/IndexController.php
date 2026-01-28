<?php

namespace App\Http\Controllers\Decisions;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\UseCases\Decisions\ListDecisions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private readonly ListDecisions $listDecisions)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $filters = $request->only(['project', 'search']);

        return Inertia::render('decisions/Index', [
            'filters' => $filters,
            'decisions' => $this->listDecisions->handle($account, $filters),
            'projects' => Project::query()->where('account_id', $account->id)->get(['id', 'name']),
        ]);
    }
}

