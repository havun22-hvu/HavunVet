<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Treatment;
use App\Models\Vaccination;
use Illuminate\Database\Eloquent\Factories\Factory;

class VaccinationFactory extends Factory
{
    protected $model = Vaccination::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'treatment_id' => null,
            'vaccine_name' => fake()->randomElement(['Rabisin', 'Nobivac DHPPi', 'Purevax RCPCh', 'Equilis Prequenza']),
            'vaccine_type' => fake()->randomElement(['kern', 'niet-kern']),
            'batch_number' => fake()->bothify('??-####'),
            'manufacturer' => fake()->company(),
            'administered_at' => now(),
            'next_due_date' => now()->addYear(),
            'administered_by' => fake()->name(),
            'notes' => null,
        ];
    }

    public function due(): static
    {
        return $this->state([
            'next_due_date' => fake()->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state([
            'next_due_date' => fake()->dateTimeBetween('+1 day', '+3 weeks'),
        ]);
    }
}
