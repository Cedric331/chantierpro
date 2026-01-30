<?php

namespace App\Http\Controllers\Milestones;

use App\Http\Controllers\Controller;
use App\Models\ProjectMilestone;
use App\UseCases\Milestones\DeleteMilestone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteMilestone $deleteMilestone)
    {
    }

    public function __invoke(Request $request, ProjectMilestone $milestone): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->deleteMilestone->handle($account, $milestone);

        return redirect()->back();
    }
}



