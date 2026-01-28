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
            'name' => $this->faker->streetName().' '.$this->faker->randomElement(['Villa', 'Résidence', 'Maison']),
            'client_name' => $this->faker->name(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'status' => $this->faker->randomElement(['preparation', 'in_progress', 'delayed', 'completed']),
            'budget' => $this->faker->numberBetween(50000, 900000),
            'start_date' => $this->faker->dateTimeBetween('-2 months', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+12 months'),
            'progress' => $this->faker->numberBetween(5, 95),
        ];
    }
}

