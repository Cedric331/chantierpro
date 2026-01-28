<?php

namespace App\Http\Controllers\Decisions;

use App\Http\Controllers\Controller;
use App\UseCases\Decisions\CreateDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateDecision $createDecision)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createDecision->handle($account, $request->all());

        return redirect()->to('/decisions');
    }
}

