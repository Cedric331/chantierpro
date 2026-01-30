<?php

use App\Models\Account;
use App\Models\Membership;
use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\ProjectTask;
use App\Models\ProjectTaskDependency;
use App\Models\User;

function createUserWithAccountForPlanning(): array
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

test('users can create phases and task dependencies', function () {
    [$user, $account] = createUserWithAccountForPlanning();
    $project = Project::factory()->create(['account_id' => $account->id]);

    $this->actingAs($user);

    $phasePayload = [
        'project_id' => $project->id,
        'title' => 'Gros oeuvre',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addWeeks(2)->toDateString(),
        'position' => 1,
    ];

    $this->post('/phases', $phasePayload)->assertRedirect();

    $phase = ProjectPhase::query()->first();
    expect($phase)->not->toBeNull();

    $taskA = ProjectTask::factory()->create([
        'account_id' => $account->id,
        'project_id' => $project->id,
        'title' => 'Préparation chantier',
    ]);
    $taskB = ProjectTask::factory()->create([
        'account_id' => $account->id,
        'project_id' => $project->id,
        'title' => 'Fondations',
    ]);

    $dependencyPayload = [
        'project_id' => $project->id,
        'task_id' => $taskB->id,
        'depends_on_task_id' => $taskA->id,
    ];

    $this->post('/task-dependencies', $dependencyPayload)->assertRedirect();

    expect(ProjectTaskDependency::query()->count())->toBe(1);
});

