<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectTask>
 */
class ProjectTaskFactory extends Factory
{
    protected $model = ProjectTask::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'project_id' => Project::factory(),
            'title' => $this->faker->sentence(4),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'done']),
            'assigned_to' => $this->faker->optional()->name(),
            'due_date' => $this->faker->optional()->dateTimeBetween('now', '+2 months'),
            'requires_photo' => $this->faker->boolean(30),
        ];
    }
}

