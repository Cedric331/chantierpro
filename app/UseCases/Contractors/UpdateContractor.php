<?php

namespace App\UseCases\Contractors;

use App\Models\Contractor;
use Illuminate\Support\Facades\Validator;

class UpdateContractor
{
    /**
     * @param array<string, mixed> $input
     */
    public function handle(Contractor $contractor, array $input): Contractor
    {
        $data = Validator::validate($input, [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'insurance_policy' => ['nullable', 'string', 'max:255'],
        ]);

        $contractor->update($data);

        return $contractor;
    }
}

