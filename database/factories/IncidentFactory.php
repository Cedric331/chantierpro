<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Incident;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'project_id' => Project::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(10),
            'status' => fake()->randomElement(['open', 'resolved']),
            'impact_days' => fake()->numberBetween(0, 14),
            'impact_cost' => fake()->numberBetween(1000, 12000),
            'reported_by' => fake()->name(),
            'resolved_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}

