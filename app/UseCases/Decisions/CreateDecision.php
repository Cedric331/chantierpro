<?php

namespace App\UseCases\Decisions;

use App\Models\Account;
use App\Models\Decision;
use App\Notifications\DecisionLogged;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class CreateDecision
{
    public function handle(Account $account, array $input): Decision
    {
        $data = Validator::make($input, [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'actor_name' => ['nullable', 'string', 'max:255'],
            'decided_at' => ['nullable', 'date'],
        ])->validate();

        $decision = Decision::create([
            ...$data,
            'account_id' => $account->id,
            'decided_at' => $data['decided_at'] ?? now(),
        ]);

        Notification::send($account->users, new DecisionLogged($decision));

        return $decision;
    }
}

