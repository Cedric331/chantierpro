<?php

namespace App\UseCases\Validations;

use App\Models\Validation;

class DeleteValidation
{
    public function handle(Validation $validation): void
    {
        $validation->delete();
    }
}

