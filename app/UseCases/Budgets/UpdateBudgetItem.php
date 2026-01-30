<?php

namespace App\UseCases\Budgets;

use App\Models\Account;
use App\Models\ProjectBudgetItem;
use App\Models\ProjectActivity;
use App\Notifications\BudgetOverrun;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateBudgetItem
{
    public function handle(Account $account, ProjectBudgetItem $item, array $input): ProjectBudgetItem
    {
        if ($item->account_id !== $account->id) {
            abort(404);
        }

        $data = Validator::make($input, [
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->where('account_id', $account->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'committed_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'variation_amount' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ])->validate();

        $item->update([
            ...$data,
            'estimated_cost' => $data['estimated_cost'] ?? 0,
            'committed_cost' => $data['committed_cost'] ?? 0,
            'actual_cost' => $data['actual_cost'] ?? 0,
            'variation_amount' => $data['variation_amount'] ?? 0,
        ]);

        $this->notifyOverrun($account, $item);

        return $item;
    }

    private function notifyOverrun(Account $account, ProjectBudgetItem $item): void
    {
        if ($item->alerted_at) {
            return;
        }

        $project = $item->project()->first();
        if (!$project || !$project->budget_alert_enabled) {
            return;
        }

        $estimated = (float) $item->estimated_cost;
        $actual = (float) $item->actual_cost;
        if ($estimated <= 0 || $actual <= 0) {
            return;
        }

        $threshold = $estimated * (1 + ($project->budget_alert_threshold / 100));
        if ($actual <= $threshold) {
            return;
        }

        Notification::send($account->users, new BudgetOverrun($item));
        if ($project) {
            ProjectActivity::create([
                'account_id' => $account->id,
                'project_id' => $project->id,
                'actor_id' => null,
                'type' => 'budget_overrun',
                'payload' => [
                    'budget_item_id' => $item->id,
                    'estimated_cost' => $item->estimated_cost,
                    'actual_cost' => $item->actual_cost,
                ],
            ]);
        }
        $item->forceFill(['alerted_at' => now()])->save();
    }
}



