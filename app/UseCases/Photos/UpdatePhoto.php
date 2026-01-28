<?php

namespace App\UseCases\Photos;

use App\Models\Photo;
use App\Models\ProjectTask;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class UpdatePhoto
{
    public function handle(Photo $photo, array $input, ?UploadedFile $file): Photo
    {
        $data = Validator::make($input, [
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'project_task_id' => ['nullable', 'integer', 'exists:project_tasks,id'],
            'caption' => ['nullable', 'string', 'max:255'],
            'taken_at' => ['nullable', 'date'],
        ])->validate();

        if (! empty($data['project_task_id'])) {
            $task = ProjectTask::query()->findOrFail($data['project_task_id']);
            $projectId = (int) ($data['project_id'] ?? $photo->project_id);
            if ($task->project_id !== $projectId || $task->account_id !== $photo->account_id) {
                abort(422, 'Intervention invalide pour ce chantier.');
            }
        }

        $photo->update($data);

        if ($file) {
            $photo->addMedia($file)->toMediaCollection('image');
        }

        return $photo;
    }
}

