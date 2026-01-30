<?php

namespace App\Http\Controllers\Budgets;

use App\Http\Controllers\Controller;
use App\Models\ProjectBudgetItem;
use App\UseCases\Budgets\DeleteBudgetItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyController extends Controller
{
    public function __construct(private readonly DeleteBudgetItem $deleteBudgetItem)
    {
    }

    public function __invoke(Request $request, ProjectBudgetItem $budgetItem): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->deleteBudgetItem->handle($account, $budgetItem);

        return redirect()->back();
    }
}



