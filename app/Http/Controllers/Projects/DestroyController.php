<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\UseCases\Projects\DeleteProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteProject $deleteProject)
    {
    }

    public function __invoke(Request $request, Project $project): RedirectResponse
    {
        if ($project->account_id !== $request->user()->current_account_id) {
            abort(404);
        }

        $this->deleteProject->handle($project);

        return redirect()->to('/projects');
    }
}

