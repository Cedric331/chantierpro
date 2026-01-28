<?php

namespace App\Http\Controllers\ProjectTasks;

use App\Http\Controllers\Controller;
use App\Models\ProjectTask;
use App\UseCases\ProjectTasks\UpdateProjectTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateProjectTask $updateProjectTask)
    {
    }

    public function __invoke(Request $request, ProjectTask $task): RedirectResponse
    {
        $this->updateProjectTask->handle($task, $request->all());

        return redirect()->back();
    }
}

