<?php

namespace App\Http\Controllers\ProjectTasks;

use App\Http\Controllers\Controller;
use App\UseCases\ProjectTasks\CreateProjectTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateProjectTask $createProjectTask)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createProjectTask->handle($account, $request->all());

        return redirect()->back();
    }
}

