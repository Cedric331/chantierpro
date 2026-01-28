<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Decision;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Decision>
 */
class DecisionFactory extends Factory
{
    protected $model = Decision::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->sentence(8),
            'actor_name' => fake()->name(),
            'decided_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}

