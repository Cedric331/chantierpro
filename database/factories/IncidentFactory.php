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
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'status' => $this->faker->randomElement(['open', 'resolved']),
            'impact_days' => $this->faker->numberBetween(0, 14),
            'impact_cost' => $this->faker->numberBetween(1000, 12000),
            'reported_by' => $this->faker->name(),
            'resolved_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}

