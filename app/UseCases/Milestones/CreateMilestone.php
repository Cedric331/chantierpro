<?php

namespace App\UseCases\Milestones;

use App\Models\Account;
use App\Models\ProjectMilestone;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateMilestone
{
    public function handle(Account $account, array $input): ProjectMilestone
    {
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

        return ProjectMilestone::create([
            ...$data,
            'account_id' => $account->id,
            'status' => $data['status'] ?? 'pending',
        ]);
    }
}



