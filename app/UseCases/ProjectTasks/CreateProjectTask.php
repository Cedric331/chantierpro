<?php

namespace App\UseCases\ProjectTasks;

use App\Models\Account;
use App\Models\ProjectTask;
use Illuminate\Support\Facades\Validator;

class CreateProjectTask
{
    public function handle(Account $account, array $input): ProjectTask
    {
        $data = Validator::make($input, [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'requires_photo' => ['nullable', 'boolean'],
        ])->validate();

        return ProjectTask::create([
            ...$data,
            'account_id' => $account->id,
            'status' => $data['status'] ?? 'pending',
            'requires_photo' => $data['requires_photo'] ?? false,
        ]);
    }
}

