<?php

namespace App\UseCases\Decisions;

use App\Models\Decision;
use Illuminate\Support\Facades\Validator;

class UpdateDecision
{
    public function handle(Decision $decision, array $input): Decision
    {
        $data = Validator::make($input, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'actor_name' => ['nullable', 'string', 'max:255'],
            'decided_at' => ['nullable', 'date'],
        ])->validate();

        $decision->update($data);

        return $decision;
    }
}

