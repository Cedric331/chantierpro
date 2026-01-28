<?php

use App\Models\Account;
use App\Models\Membership;
use App\Models\Project;
use App\Models\User;

test('users can create validations', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['trial_ends_at' => now()->addDays(14)]);
    Membership::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
    ]);
    $user->forceFill(['current_account_id' => $account->id])->save();

    $project = Project::factory()->create(['account_id' => $account->id]);

    $this->actingAs($user);

    $response = $this->post('/validations', [
        'project_id' => $project->id,
        'title' => 'Plan cuisine modifié',
        'type' => 'Plan',
        'status' => 'pending',
        'requested_by' => 'Jean Moreau',
    ]);

    $response->assertRedirect('/validations');
    $this->assertDatabaseHas('validations', [
        'account_id' => $account->id,
        'title' => 'Plan cuisine modifié',
    ]);
});

