<?php

namespace App\UseCases\Contractors;

use App\Models\Contractor;

class DeleteContractor
{
    public function handle(Contractor $contractor): void
    {
        $contractor->delete();
    }
}

