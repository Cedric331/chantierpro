<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Account;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $validated = Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $accountName = $validated['name'].' Account';
            $account = Account::create([
                'name' => $accountName,
                'slug' => Str::slug($accountName).'-'.Str::lower(Str::random(6)),
                'trial_ends_at' => Carbon::now()->addDays(14),
            ]);

            Membership::create([
                'account_id' => $account->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);

            $user->forceFill([
                'current_account_id' => $account->id,
            ])->save();

            $user->assignRole('Customer');

            return $user;
        });
    }
}
