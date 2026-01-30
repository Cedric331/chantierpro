<?php

namespace App\Http\Controllers\ProjectTaskDependencies;

use App\Http\Controllers\Controller;
use App\UseCases\ProjectTaskDependencies\CreateProjectTaskDependency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateProjectTaskDependency $createProjectTaskDependency)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createProjectTaskDependency->handle($account, $request->all());

        return redirect()->back();
    }
}

