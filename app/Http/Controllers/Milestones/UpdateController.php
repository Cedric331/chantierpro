<?php

namespace App\Http\Controllers\Milestones;

use App\Http\Controllers\Controller;
use App\Models\ProjectMilestone;
use App\UseCases\Milestones\UpdateMilestone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateMilestone $updateMilestone)
    {
    }

    public function __invoke(Request $request, ProjectMilestone $milestone): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->updateMilestone->handle($account, $milestone, $request->all());

        return redirect()->back();
    }
}



