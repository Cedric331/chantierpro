<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\UseCases\Projects\CreateProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateProject $createProject)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createProject->handle($account, $request->all());

        return redirect()->to('/projects');
    }
}

