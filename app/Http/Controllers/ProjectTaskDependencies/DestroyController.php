<?php

namespace App\Http\Controllers\ProjectTaskDependencies;

use App\Http\Controllers\Controller;
use App\Models\ProjectTaskDependency;
use App\UseCases\ProjectTaskDependencies\DeleteProjectTaskDependency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteProjectTaskDependency $deleteProjectTaskDependency)
    {
    }

    public function __invoke(Request $request, ProjectTaskDependency $dependency): RedirectResponse
    {
        if ($dependency->account_id !== $request->user()->current_account_id) {
            abort(404);
        }

        $this->deleteProjectTaskDependency->handle($dependency);

        return redirect()->back();
    }
}

