<?php

namespace App\UseCases\ProjectTasks;

use App\Models\Account;
use App\Models\ProjectPhase;
use App\Models\ProjectTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateProjectTask
{
    public function handle(Account $account, array $input): ProjectTask
    {
        $data = Validator::make($input, [
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->where('account_id', $account->id),
            ],
            'phase_id' => [
                'nullable',
                'integer',
                Rule::exists('project_phases', 'id')->where('account_id', $account->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'due_date' => ['nullable', 'date'],
            'requires_photo' => ['nullable', 'boolean'],
        ])->validate();

        if (! empty($data['phase_id'])) {
            $phase = ProjectPhase::query()->where('account_id', $account->id)->findOrFail($data['phase_id']);
            if ($phase->project_id !== (int) $data['project_id']) {
                abort(422, 'La phase doit appartenir au même chantier.');
            }
        }

        $this->normalizeSchedule($data);

        return ProjectTask::create([
            ...$data,
            'account_id' => $account->id,
            'status' => $data['status'] ?? 'pending',
            'requires_photo' => $data['requires_photo'] ?? false,
            'progress' => $data['progress'] ?? 0,
        ]);
    }

    private function normalizeSchedule(array &$data): void
    {
        $start = isset($data['start_date']) ? Carbon::parse($data['start_date']) : null;
        $end = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;
        $duration = $data['duration_days'] ?? null;

        if ($start && $duration && !$end) {
            $data['end_date'] = $start->copy()->addDays($duration - 1)->toDateString();
        } elseif ($start && $end && !$duration) {
            $data['duration_days'] = $start->diffInDays($end) + 1;
        }
    }
}

