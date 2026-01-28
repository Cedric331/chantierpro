<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Contractor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contractor>
 */
class ContractorFactory extends Factory
{
    protected $model = Contractor::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->name(),
            'company' => fake()->company(),
            'role' => fake()->randomElement(['Électricien', 'Plombier', 'Maçon', 'Peintre']),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'insurance_policy' => 'POL-'.fake()->numerify('#####'),
        ];
    }
}

