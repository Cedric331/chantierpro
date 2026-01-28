<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\MembershipRole;
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
            'account_name' => ['required', 'string', 'max:255'],
            'account_address' => ['nullable', 'string', 'max:255'],
            'account_city' => ['nullable', 'string', 'max:255'],
            'account_phone' => ['nullable', 'string', 'max:50'],
        ])->validate();

        return DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $accountName = $validated['account_name'];
            $account = Account::create([
                'name' => $accountName,
                'address' => $validated['account_address'] ?? null,
                'city' => $validated['account_city'] ?? null,
                'phone' => $validated['account_phone'] ?? null,
                'slug' => Str::slug($accountName).'-'.Str::lower(Str::random(6)),
                'trial_ends_at' => Carbon::now()->addDays(14),
            ]);

            Membership::create([
                'account_id' => $account->id,
                'user_id' => $user->id,
                'role' => MembershipRole::Owner,
            ]);

            $user->forceFill([
                'current_account_id' => $account->id,
            ])->save();

            $user->assignRole('Customer');

            return $user;
        });
    }
}
