<?php

use App\Models\Account;
use App\Models\Membership;
use App\Models\Project;
use App\Models\ProjectBudgetItem;
use App\Models\User;
use App\Notifications\BudgetOverrun;
use Illuminate\Support\Facades\Notification;

function createUserWithAccountForBudget(): array
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

test('budget overrun triggers notification and marks alert', function () {
    [$user, $account] = createUserWithAccountForBudget();
    $project = Project::factory()->create([
        'account_id' => $account->id,
        'budget_alert_enabled' => true,
        'budget_alert_threshold' => 0,
    ]);

    Notification::fake();

    $this->actingAs($user);

    $payload = [
        'project_id' => $project->id,
        'name' => 'Gros oeuvre',
        'estimated_cost' => 1000,
        'actual_cost' => 1500,
    ];

    $response = $this->post('/budgets', $payload);
    $response->assertRedirect();

    $item = ProjectBudgetItem::query()->first();
    expect($item)->not->toBeNull();
    expect($item->alerted_at)->not->toBeNull();

    Notification::assertSentTo($user, BudgetOverrun::class);
});

