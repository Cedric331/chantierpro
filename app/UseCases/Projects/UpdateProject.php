<?php

namespace App\UseCases\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\Validator;

class UpdateProject
{
    /**
     * @param array<string, mixed> $input
     */
    public function handle(Project $project, array $input): Project
    {
        $data = Validator::validate($input, [
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'budget_alert_enabled' => ['nullable', 'boolean'],
            'budget_alert_threshold' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        if (! array_key_exists('progress', $data) || $data['progress'] === null || $data['progress'] === '') {
            $data['progress'] = $project->progress;
        }
        if (! array_key_exists('budget', $data) || $data['budget'] === null || $data['budget'] === '') {
            $data['budget'] = $project->budget;
        }
        if (! array_key_exists('budget_alert_enabled', $data) || $data['budget_alert_enabled'] === null) {
            $data['budget_alert_enabled'] = $project->budget_alert_enabled;
        }
        if (! array_key_exists('budget_alert_threshold', $data) || $data['budget_alert_threshold'] === null) {
            $data['budget_alert_threshold'] = $project->budget_alert_threshold;
        }

        $project->update($data);

        return $project;
    }
}

