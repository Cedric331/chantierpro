<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Project;
use App\Models\Validation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Validation>
 */
class ValidationFactory extends Factory
{
    protected $model = Validation::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'project_id' => Project::factory(),
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(['Plan', 'Matériau', 'Modification']),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'requested_by' => fake()->name(),
            'decided_by' => fake()->name(),
            'decided_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}

