<?php

namespace App\Http\Controllers\ProjectPhases;

use App\Http\Controllers\Controller;
use App\Models\ProjectPhase;
use App\UseCases\ProjectPhases\UpdateProjectPhase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateProjectPhase $updateProjectPhase)
    {
    }

    public function __invoke(Request $request, ProjectPhase $phase): RedirectResponse
    {
        if ($phase->account_id !== $request->user()->current_account_id) {
            abort(404);
        }

        $this->updateProjectPhase->handle($phase, $request->all());

        return redirect()->back();
    }
}

