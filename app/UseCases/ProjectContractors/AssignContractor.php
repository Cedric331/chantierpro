<?php

namespace App\UseCases\ProjectContractors;

use App\Models\Contractor;
use App\Models\Project;
use Illuminate\Support\Facades\Validator;

class AssignContractor
{
    public function handle(Project $project, array $input): void
    {
        $data = Validator::make($input, [
            'contractor_id' => ['required', 'integer', 'exists:contractors,id'],
            'role' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $contractor = Contractor::query()->findOrFail($data['contractor_id']);

        if ($contractor->account_id !== $project->account_id) {
            abort(403);
        }

        $project->contractors()->syncWithoutDetaching([
            $contractor->id => ['role' => $data['role'] ?? null],
        ]);
    }
}

