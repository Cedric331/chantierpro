<?php

namespace App\Http\Controllers\ProjectPhases;

use App\Http\Controllers\Controller;
use App\Models\ProjectPhase;
use App\UseCases\ProjectPhases\DeleteProjectPhase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteProjectPhase $deleteProjectPhase)
    {
    }

    public function __invoke(Request $request, ProjectPhase $phase): RedirectResponse
    {
        if ($phase->account_id !== $request->user()->current_account_id) {
            abort(404);
        }

        $this->deleteProjectPhase->handle($phase);

        return redirect()->back();
    }
}

