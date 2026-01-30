<?php

namespace App\Http\Controllers\Milestones;

use App\Http\Controllers\Controller;
use App\UseCases\Milestones\CreateMilestone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateMilestone $createMilestone)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createMilestone->handle($account, $request->all());

        return redirect()->back();
    }
}



