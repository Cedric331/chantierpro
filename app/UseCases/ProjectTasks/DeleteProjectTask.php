<?php

namespace App\UseCases\ProjectTasks;

use App\Models\ProjectTask;

class DeleteProjectTask
{
    public function handle(ProjectTask $task): void
    {
        $task->delete();
    }
}

