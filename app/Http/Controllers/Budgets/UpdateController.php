<?php

namespace App\Http\Controllers\Budgets;

use App\Http\Controllers\Controller;
use App\Models\ProjectBudgetItem;
use App\UseCases\Budgets\UpdateBudgetItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(private readonly UpdateBudgetItem $updateBudgetItem)
    {
    }

    public function __invoke(Request $request, ProjectBudgetItem $budgetItem): RedirectResponse
    {
        $account = $request->user()->currentAccount;
        $this->updateBudgetItem->handle($account, $budgetItem, $request->all());

        return redirect()->back();
    }
}



