<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->streetName().' '.fake()->randomElement(['Villa', 'Résidence', 'Maison']),
            'client_name' => fake()->name(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'status' => fake()->randomElement(['preparation', 'in_progress', 'delayed', 'completed']),
            'budget' => fake()->numberBetween(50000, 900000),
            'start_date' => fake()->dateTimeBetween('-2 months', '+1 month'),
            'end_date' => fake()->dateTimeBetween('+2 months', '+12 months'),
            'progress' => fake()->numberBetween(5, 95),
        ];
    }
}

