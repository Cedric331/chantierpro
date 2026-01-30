<?php

namespace App\UseCases\ProjectMessages;

use App\Models\Account;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectMessage;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateProjectMessage
{
    public function handle(Account $account, User $author, array $input): ProjectMessage
    {
        $data = Validator::make($input, [
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->where('account_id', $account->id),
            ],
            'body' => ['required', 'string'],
        ])->validate();

        $message = ProjectMessage::create([
            ...$data,
            'account_id' => $account->id,
            'author_id' => $author->id,
        ]);

        $project = Project::query()->where('id', $data['project_id'])->first();
        if ($project) {
            ProjectActivity::create([
                'account_id' => $account->id,
                'project_id' => $project->id,
                'actor_id' => $author->id,
                'type' => 'message_posted',
                'payload' => [
                    'message_id' => $message->id,
                    'body' => $message->body,
                ],
            ]);
        }

        return $message;
    }
}

