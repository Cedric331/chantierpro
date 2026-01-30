<?php

namespace App\UseCases\ProjectPhases;

use App\Models\ProjectPhase;

class DeleteProjectPhase
{
    public function handle(ProjectPhase $phase): void
    {
        $phase->delete();
    }
}

