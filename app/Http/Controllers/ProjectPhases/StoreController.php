<?php

namespace App\Http\Controllers\ProjectPhases;

use App\Http\Controllers\Controller;
use App\UseCases\ProjectPhases\CreateProjectPhase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateProjectPhase $createProjectPhase)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createProjectPhase->handle($account, $request->all());

        return redirect()->back();
    }
}

