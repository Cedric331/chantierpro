<?php

namespace App\UseCases\Comments;

use App\Models\Account;
use App\Models\Comment;
use App\Models\Decision;
use App\Models\ProjectActivity;
use App\Models\ProjectBudgetItem;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class CreateComment
{
    public function handle(Account $account, User $author, array $input): Comment
    {
        $data = Validator::make($input, [
            'commentable_type' => ['required', 'string'],
            'commentable_id' => ['required', 'integer'],
            'body' => ['required', 'string'],
        ])->validate();

        $commentable = $this->resolveCommentable($account, $data['commentable_type'], $data['commentable_id']);

        $comment = $commentable->comments()->create([
            'account_id' => $account->id,
            'author_id' => $author->id,
            'body' => $data['body'],
        ]);

        $projectId = $commentable->project_id ?? null;
        if ($projectId) {
            ProjectActivity::create([
                'account_id' => $account->id,
                'project_id' => $projectId,
                'actor_id' => $author->id,
                'type' => 'comment_added',
                'payload' => [
                    'comment_id' => $comment->id,
                    'commentable_type' => $data['commentable_type'],
                    'commentable_id' => $data['commentable_id'],
                ],
            ]);
        }

        return $comment;
    }

    private function resolveCommentable(Account $account, string $type, int $id): Model
    {
        return match ($type) {
            'task' => ProjectTask::query()->where('account_id', $account->id)->findOrFail($id),
            'milestone' => ProjectMilestone::query()->where('account_id', $account->id)->findOrFail($id),
            'decision' => Decision::query()->where('account_id', $account->id)->findOrFail($id),
            'budget_item' => ProjectBudgetItem::query()->where('account_id', $account->id)->findOrFail($id),
            default => abort(422, 'Type de commentaire non supporté.'),
        };
    }
}

