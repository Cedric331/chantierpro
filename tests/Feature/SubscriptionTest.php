<?php

use App\Models\Account;
use App\Models\Membership;
use App\Models\User;

test('users without subscription are redirected to billing', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'trial_ends_at' => now()->subDay(),
    ]);
    Membership::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
    ]);
    $user->forceFill(['current_account_id' => $account->id])->save();

    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertRedirect(route('billing.index'));
});

