<?php

namespace App\UseCases\Projects;

use App\Models\Account;
use App\Models\Project;
use Illuminate\Support\Facades\Validator;

class CreateProject
{
    public function handle(Account $account, array $input): Project
    {
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'budget_alert_enabled' => ['nullable', 'boolean'],
            'budget_alert_threshold' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
        ])->validate();

        return Project::create([
            ...$data,
            'account_id' => $account->id,
        ]);
    }
}