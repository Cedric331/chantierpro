<?php

namespace App\Http\Controllers\ProjectContractors;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\UseCases\ProjectContractors\AssignContractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly AssignContractor $assignContractor)
    {
    }

    public function __invoke(Request $request, Project $project): RedirectResponse
    {
        if ($project->account_id !== $request->user()->current_account_id) {
            abort(404);
        }

        $this->assignContractor->handle($project, $request->all());

        return redirect()->to('/projects/'.$project->id);
    }
}

