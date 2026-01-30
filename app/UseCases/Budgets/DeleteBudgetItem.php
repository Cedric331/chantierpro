<?php

namespace App\UseCases\Budgets;

use App\Models\Account;
use App\Models\ProjectBudgetItem;

class DeleteBudgetItem
{
    public function handle(Account $account, ProjectBudgetItem $item): void
    {
        if ($item->account_id !== $account->id) {
            abort(404);
        }

        $item->delete();
    }
}



