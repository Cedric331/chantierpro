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
            'name' => $this->faker->name(),
            'company' => $this->faker->company(),
            'role' => $this->faker->randomElement(['Électricien', 'Plombier', 'Maçon', 'Peintre']),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'insurance_policy' => 'POL-'.$this->faker->numerify('#####'),
        ];
    }
}

