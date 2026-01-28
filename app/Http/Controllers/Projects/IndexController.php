<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\UseCases\Projects\ListProjects;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends Controller
{
    public function __construct(private readonly ListProjects $listProjects)
    {
    }

    public function __invoke(Request $request): Response
    {
        $account = $request->user()->currentAccount;
        $filters = $request->only(['status', 'city', 'client', 'search', 'view']);

        return Inertia::render('projects/Index', [
            'filters' => $filters,
            'projects' => $this->listProjects->handle($account, $filters),
        ]);
    }
}

