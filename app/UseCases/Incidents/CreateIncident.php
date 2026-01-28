<?php

namespace App\UseCases\Incidents;

use App\Models\Account;
use App\Models\Incident;
use App\Notifications\IncidentReported;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class CreateIncident
{
    public function handle(Account $account, array $input): Incident
    {
        $data = Validator::make($input, [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:50'],
            'impact_days' => ['nullable', 'integer', 'min:0'],
            'impact_cost' => ['nullable', 'numeric', 'min:0'],
            'reported_by' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $incident = Incident::create([
            ...$data,
            'account_id' => $account->id,
            'status' => $data['status'] ?? 'open',
        ]);

        Notification::send($account->users, new IncidentReported($incident));

        return $incident;
    }
}

