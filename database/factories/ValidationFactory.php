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
            'title' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement(['Plan', 'Matériau', 'Modification']),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'requested_by' => $this->faker->name(),
            'decided_by' => $this->faker->name(),
            'decided_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}

