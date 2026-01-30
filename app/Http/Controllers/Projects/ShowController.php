<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowController extends Controller
{
    public function __invoke(Request $request, Project $project): Response
    {
        if ($project->account_id !== $request->user()->current_account_id) {
            abort(404);
        }

        $project->load([
            'contractors',
            'documents',
            'validations',
            'incidents',
            'tasks',
            'milestones',
            'decisions',
            'photos',
            'messages.author',
            'activities.actor',
            'tasks.comments.author',
            'milestones.comments.author',
            'decisions.comments.author',
            'budgetItems.comments.author',
        ]);

        return Inertia::render('projects/Show', [
            'project' => $project,
            'contractorsCatalog' => Contractor::query()
                ->where('account_id', $request->user()->current_account_id)
                ->get(['id', 'name', 'role', 'company']),
        ]);
    }
}

