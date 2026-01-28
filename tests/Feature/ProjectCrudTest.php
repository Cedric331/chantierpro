<?php

use App\Models\Account;
use App\Models\Membership;
use App\Models\Project;
use App\Models\User;

function createUserWithAccount(): array
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

test('users can list their projects', function () {
    [$user, $account] = createUserWithAccount();
    Project::factory()->count(2)->create(['account_id' => $account->id]);

    $this->actingAs($user);

    $response = $this->get('/projects');
    $response->assertOk();
});

test('users can create a project', function () {
    [$user, $account] = createUserWithAccount();

    $this->actingAs($user);

    $payload = [
        'name' => 'Villa Riviera',
        'client_name' => 'Jean Martin',
        'address' => '12 Avenue du Port',
        'city' => 'Marseille',
        'status' => 'preparation',
        'budget' => 350000,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(6)->toDateString(),
        'progress' => 5,
    ];

    $response = $this->post('/projects', $payload);
    $response->assertRedirect('/projects');

    $this->assertDatabaseHas('projects', [
        'account_id' => $account->id,
        'name' => 'Villa Riviera',
    ]);
});

