<?php

namespace App\Http\Controllers\ProjectContractors;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Project;
use App\UseCases\ProjectContractors\RemoveContractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyController extends Controller
{
    public function __construct(private readonly RemoveContractor $removeContractor)
    {
    }

    public function __invoke(Request $request, Project $project, Contractor $contractor): RedirectResponse
    {
        if ($project->account_id !== $request->user()->current_account_id) {
            abort(404);
        }

        $this->removeContractor->handle($project, $contractor);

        return redirect()->to('/projects/'.$project->id);
    }
}

