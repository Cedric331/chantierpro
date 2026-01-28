<?php

namespace App\UseCases\Photos;

use App\Models\Account;
use App\Models\Photo;
use App\Models\ProjectTask;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class CreatePhoto
{
    public function handle(Account $account, array $input, UploadedFile $file): Photo
    {
        $data = Validator::make($input, [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'project_task_id' => ['nullable', 'integer', 'exists:project_tasks,id'],
            'caption' => ['nullable', 'string', 'max:255'],
            'taken_at' => ['nullable', 'date'],
        ])->validate();

        if (! empty($data['project_task_id'])) {
            $task = ProjectTask::query()->findOrFail($data['project_task_id']);
            if ($task->project_id !== (int) $data['project_id'] || $task->account_id !== $account->id) {
                abort(422, 'Intervention invalide pour ce chantier.');
            }
        }

        $photo = Photo::create([
            ...$data,
            'account_id' => $account->id,
        ]);

        $photo->addMedia($file)->toMediaCollection('image');

        return $photo;
    }
}

