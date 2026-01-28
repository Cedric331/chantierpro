<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Photo;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'project_id' => Project::factory(),
            'caption' => fake()->sentence(3),
            'taken_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}

