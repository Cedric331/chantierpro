<?php

namespace App\Http\Controllers\Incidents;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\UseCases\Incidents\ListIncidents;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private readonly ListIncidents $listIncidents)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $filters = $request->only(['project', 'status', 'search']);

        return Inertia::render('incidents/Index', [
            'filters' => $filters,
            'incidents' => $this->listIncidents->handle($account, $filters),
            'projects' => Project::query()->where('account_id', $account->id)->get(['id', 'name']),
        ]);
    }
}

