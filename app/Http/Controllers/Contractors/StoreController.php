<?php

namespace App\Http\Controllers\Contractors;

use App\Http\Controllers\Controller;
use App\UseCases\Contractors\CreateContractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateContractor $createContractor)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createContractor->handle($account, $request->all());

        return redirect()->to('/contractors');
    }
}

