<?php

namespace App\UseCases\ProjectContractors;

use App\Models\Contractor;
use App\Models\Project;

class RemoveContractor
{
    public function handle(Project $project, Contractor $contractor): void
    {
        if ($contractor->account_id !== $project->account_id) {
            abort(403);
        }

        $project->contractors()->detach($contractor->id);
    }
}

