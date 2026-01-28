<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'project_id' => Project::factory(),
            'title' => fake()->sentence(3),
            'category' => fake()->randomElement(['Plans', 'Devis', 'Factures']),
            'version' => 'v'.fake()->numberBetween(1, 3),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}

