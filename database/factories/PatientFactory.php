<?php

namespace Database\Factories;

use App\Models\Owner;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'owner_id' => Owner::factory(),
            'name' => fake()->firstName(),
            'species' => fake()->randomElement(['hond', 'kat', 'konijn', 'paard']),
            'breed' => fake()->optional()->word(),
            'date_of_birth' => fake()->dateTimeBetween('-15 years', '-6 months'),
            'gender' => fake()->randomElement(['male', 'female', 'unknown']),
            'neutered' => fake()->boolean(),
            'chip_number' => fake()->optional()->numerify('###############'),
            'weight' => fake()->optional()->randomFloat(2, 0.5, 80),
            'color' => fake()->optional()->safeColorName(),
            'coat_type' => fake()->optional()->randomElement(['kort', 'lang', 'draad', 'krullend']),
            'allergies' => null,
            'notes' => fake()->optional()->sentence(),
            'photo_path' => null,
            'deceased_at' => null,
        ];
    }

    public function deceased(): static
    {
        return $this->state([
            'deceased_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function dog(): static
    {
        return $this->state(['species' => 'hond']);
    }

    public function cat(): static
    {
        return $this->state(['species' => 'kat']);
    }
}
