<?php

namespace App\Http\Controllers\Budgets;

use App\Http\Controllers\Controller;
use App\UseCases\Budgets\CreateBudgetItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private readonly CreateBudgetItem $createBudgetItem)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->createBudgetItem->handle($account, $request->all());

        return redirect()->back();
    }
}



