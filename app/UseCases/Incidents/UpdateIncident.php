<?php

namespace App\UseCases\Incidents;

use App\Models\Incident;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class UpdateIncident
{
    /**
     * @param array<string, mixed> $input
     */
    public function handle(Incident $incident, array $input): Incident
    {
        $data = Validator::validate($input, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
            'impact_days' => ['nullable', 'integer', 'min:0'],
            'impact_cost' => ['nullable', 'numeric', 'min:0'],
            'reported_by' => ['nullable', 'string', 'max:255'],
        ]);

        $incident->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'impact_days' => $data['impact_days'] ?? 0,
            'impact_cost' => $data['impact_cost'] ?? 0,
            'reported_by' => $data['reported_by'] ?? null,
            'resolved_at' => $data['status'] === 'resolved' ? Carbon::now() : null,
        ]);

        return $incident;
    }
}

