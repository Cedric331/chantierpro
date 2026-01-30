<?php

namespace App\UseCases\ProjectTaskDependencies;

use App\Models\ProjectTaskDependency;

class DeleteProjectTaskDependency
{
    public function handle(ProjectTaskDependency $dependency): void
    {
        $dependency->delete();
    }
}

