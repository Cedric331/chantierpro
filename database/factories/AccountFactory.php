<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
      $name = $this->faker->company();

        return [
            'name' => $name,
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'phone' => $this->faker->phoneNumber(),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'trial_ends_at' => now()->addDays(14),
        ];
    }
}

