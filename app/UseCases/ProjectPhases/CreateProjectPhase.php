<?php

namespace App\UseCases\ProjectPhases;

use App\Models\Account;
use App\Models\ProjectPhase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateProjectPhase
{
    public function handle(Account $account, array $input): ProjectPhase
    {
        $data = Validator::make($input, [
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->where('account_id', $account->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'position' => ['nullable', 'integer', 'min:0'],
        ])->validate();

        return ProjectPhase::create([
            ...$data,
            'account_id' => $account->id,
            'position' => $data['position'] ?? 0,
        ]);
    }
}

