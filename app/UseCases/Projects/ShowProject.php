<?php

namespace App\UseCases\Projects;

use App\Models\Project;

class ShowProject
{
    public function handle(Project $project): Project
    {
        return $project->load([
            'contractors',
            'documents',
            'validations',
            'incidents',
            'tasks',
            'decisions',
            'photos',
        ]);
    }
}

