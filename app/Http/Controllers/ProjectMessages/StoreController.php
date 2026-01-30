<?php

namespace App\Http\Controllers\ProjectMessages;

use App\Http\Controllers\Controller;
use App\UseCases\ProjectMessages\CreateProjectMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateProjectMessage $createProjectMessage)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createProjectMessage->handle($account, $request->user(), $request->all());

        return redirect()->back();
    }
}

