<?php

use App\Models\Account;
use App\Models\Membership;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectMessage;
use App\Models\ProjectTask;
use App\Models\User;

function createUserWithAccountForCommunication(): array
{
    $user = User::factory()->create();
    $account = Account::factory()->create(['trial_ends_at' => now()->addDays(14)]);
    Membership::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
    ]);
    $user->forceFill(['current_account_id' => $account->id])->save();

    return [$user, $account];
}

test('users can post project messages and comments with activity log', function () {
    [$user, $account] = createUserWithAccountForCommunication();
    $project = Project::factory()->create(['account_id' => $account->id]);
    $task = ProjectTask::factory()->create([
        'account_id' => $account->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user);

    $messagePayload = [
        'project_id' => $project->id,
        'body' => 'Point chantier du jour.',
    ];

    $this->post('/project-messages', $messagePayload)->assertRedirect();

    expect(ProjectMessage::query()->count())->toBe(1);
    expect(ProjectActivity::query()->where('type', 'message_posted')->exists())->toBeTrue();

    $commentPayload = [
        'commentable_type' => 'task',
        'commentable_id' => $task->id,
        'body' => 'Prévoir un point sécurité.',
    ];

    $this->post('/comments', $commentPayload)->assertRedirect();

    expect(ProjectActivity::query()->where('type', 'comment_added')->exists())->toBeTrue();
});

