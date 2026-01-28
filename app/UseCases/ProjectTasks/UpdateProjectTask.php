<?php

namespace App\UseCases\ProjectTasks;

use App\Models\ProjectTask;
use Illuminate\Support\Facades\Validator;

class UpdateProjectTask
{
    public function handle(ProjectTask $task, array $input): ProjectTask
    {
        $data = Validator::make($input, [
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'requires_photo' => ['nullable', 'boolean'],
        ])->validate();

        $task->update($data);

        return $task;
    }
}

