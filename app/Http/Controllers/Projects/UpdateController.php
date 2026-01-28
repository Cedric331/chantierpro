<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\UseCases\Projects\UpdateProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateProject $updateProject)
    {
    }

    public function __invoke(Request $request, Project $project): RedirectResponse
    {
        if ($project->account_id !== $request->user()->current_account_id) {
            abort(404);
        }

        $this->updateProject->handle($project, $request->all());

        return redirect()->to('/projects/'.$project->id);
    }
}

