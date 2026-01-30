<?php

namespace App\UseCases\Milestones;

use App\Models\Account;
use App\Models\ProjectMilestone;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateMilestone
{
    public function handle(Account $account, ProjectMilestone $milestone, array $input): ProjectMilestone
    {
        if ($milestone->account_id !== $account->id) {
            abort(404);
        }

        $data = Validator::make($input, [
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->where('account_id', $account->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'due_date' => ['nullable', 'date'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ])->validate();

        $milestone->update([
            ...$data,
            'status' => $data['status'] ?? 'pending',
        ]);

        return $milestone;
    }
}



