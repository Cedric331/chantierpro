<?php

namespace App\UseCases\Validations;

use App\Models\Validation;
use Illuminate\Support\Facades\Validator;

class UpdateValidation
{
    public function handle(Validation $validation, array $input): Validation
    {
        $data = Validator::make($input, [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'requested_by' => ['nullable', 'string', 'max:255'],
            'decided_by' => ['nullable', 'string', 'max:255'],
            'decided_at' => ['nullable', 'date'],
        ])->validate();

        if (in_array($data['status'] ?? '', ['approved', 'rejected'], true) && empty($data['decided_at'])) {
            $data['decided_at'] = now();
        }

        $validation->update($data);

        return $validation;
    }
}

