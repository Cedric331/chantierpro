<?php

namespace App\UseCases\Milestones;

use App\Models\Account;
use App\Models\ProjectMilestone;

class DeleteMilestone
{
    public function handle(Account $account, ProjectMilestone $milestone): void
    {
        if ($milestone->account_id !== $account->id) {
            abort(404);
        }

        $milestone->delete();
    }
}



