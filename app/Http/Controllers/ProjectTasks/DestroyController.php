<?php

namespace App\Http\Controllers\ProjectTasks;

use App\Http\Controllers\Controller;
use App\Models\ProjectTask;
use App\UseCases\ProjectTasks\DeleteProjectTask;
use Illuminate\Http\RedirectResponse;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteProjectTask $deleteProjectTask)
    {
    }

    public function __invoke(ProjectTask $task): RedirectResponse
    {
        $this->deleteProjectTask->handle($task);

        return redirect()->back();
    }
}

