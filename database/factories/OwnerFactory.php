<?php

namespace Database\Factories;

use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

class OwnerFactory extends Factory
{
    protected $model = Owner::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'phone2' => fake()->optional()->phoneNumber(),
            'address' => fake()->streetName(),
            'house_number' => fake()->buildingNumber(),
            'postal_code' => fake()->numerify('####') . fake()->lexify('??'),
            'city' => fake()->city(),
            'ubn' => fake()->optional()->numerify('######'),
            'notes' => fake()->optional()->sentence(),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
