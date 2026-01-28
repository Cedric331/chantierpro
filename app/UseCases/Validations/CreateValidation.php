<?php

namespace App\UseCases\Validations;

use App\Models\Account;
use App\Models\Validation;
use App\Notifications\ValidationRequested;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class CreateValidation
{
    public function handle(Account $account, array $input): Validation
    {
        $data = Validator::make($input, [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'requested_by' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $validation = Validation::create([
            ...$data,
            'account_id' => $account->id,
            'status' => $data['status'] ?? 'pending',
        ]);

        Notification::send($account->users, new ValidationRequested($validation));

        return $validation;
    }
}

