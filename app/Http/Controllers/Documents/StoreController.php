<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\UseCases\Documents\CreateDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateDocument $createDocument)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createDocument->handle($account, $request->all(), $request->file('file'));

        return redirect()->back();
    }
}

