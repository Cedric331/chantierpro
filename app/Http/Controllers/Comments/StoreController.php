<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\UseCases\Comments\CreateComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateComment $createComment)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createComment->handle($account, $request->user(), $request->all());

        return redirect()->back();
    }
}

