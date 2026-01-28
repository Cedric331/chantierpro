<?php

namespace App\UseCases\Contractors;

use App\Models\Account;
use App\Models\Contractor;
use Illuminate\Support\Facades\Validator;

class CreateContractor
{
    public function handle(Account $account, array $input): Contractor
    {
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'insurance_policy' => ['nullable', 'string', 'max:255'],
        ])->validate();

        return Contractor::create([
            ...$data,
            'account_id' => $account->id,
        ]);
    }
}

