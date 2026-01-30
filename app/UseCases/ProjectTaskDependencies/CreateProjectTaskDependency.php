<?php

namespace App\UseCases\ProjectTaskDependencies;

use App\Models\Account;
use App\Models\ProjectTask;
use App\Models\ProjectTaskDependency;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateProjectTaskDependency
{
    public function handle(Account $account, array $input): ProjectTaskDependency
    {
        $data = Validator::make($input, [
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->where('account_id', $account->id),
            ],
            'task_id' => [
                'required',
                'integer',
                Rule::exists('project_tasks', 'id')->where('account_id', $account->id),
            ],
            'depends_on_task_id' => [
                'required',
                'integer',
                Rule::exists('project_tasks', 'id')->where('account_id', $account->id),
            ],
            'dependency_type' => ['nullable', 'string', 'max:50'],
        ])->validate();

        if ($data['task_id'] === $data['depends_on_task_id']) {
            abort(422, 'Une tâche ne peut pas dépendre d’elle-même.');
        }

        $task = ProjectTask::query()->where('account_id', $account->id)->findOrFail($data['task_id']);
        $dependsOn = ProjectTask::query()->where('account_id', $account->id)->findOrFail($data['depends_on_task_id']);
        if ($task->project_id !== $dependsOn->project_id || $task->project_id !== (int) $data['project_id']) {
            abort(422, 'Les tâches doivent appartenir au même chantier.');
        }

        return ProjectTaskDependency::create([
            ...$data,
            'account_id' => $account->id,
            'dependency_type' => $data['dependency_type'] ?? 'finish_to_start',
        ]);
    }
}

