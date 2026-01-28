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
            'title' => $this->faker->sentence(3),
            'category' => $this->faker->randomElement(['Plans', 'Devis', 'Factures']),
            'version' => 'v'.$this->faker->numberBetween(1, 3),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}

