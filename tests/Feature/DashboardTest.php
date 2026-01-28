<?php

use App\Models\Account;
use App\Models\Membership;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['trial_ends_at' => now()->addDays(14)]);
    Membership::factory()->create([
        'user_id' => $user->id,
        'account_id' => $account->id,
    ]);
    $user->forceFill(['current_account_id' => $account->id])->save();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});