<?php

namespace App\UseCases\ProjectPhases;

use App\Models\ProjectPhase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateProjectPhase
{
    public function handle(ProjectPhase $phase, array $input): ProjectPhase
    {
        $data = Validator::make($input, [
            'project_id' => [
                'nullable',
                'integer',
                Rule::exists('projects', 'id')->where('account_id', $phase->account_id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'position' => ['nullable', 'integer', 'min:0'],
        ])->validate();

        $phase->update($data);

        return $phase;
    }
}

