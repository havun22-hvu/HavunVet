<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Treatment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'treatment_id' => null,
            'medication_id' => null,
            'medication_name' => fake()->word() . 'cillin',
            'dosage' => fake()->randomElement(['1 tablet', '2 ml', '5 druppels']),
            'frequency' => fake()->randomElement(['1x daags', '2x daags', '3x daags']),
            'duration_days' => fake()->optional()->numberBetween(3, 30),
            'instructions' => fake()->optional()->sentence(),
            'dispensed_quantity' => fake()->optional()->randomFloat(2, 1, 50),
            'dispensed_unit' => 'stuks',
            'dispensed_at' => null,
            'prescribed_by' => fake()->name(),
        ];
    }

    public function dispensed(): static
    {
        return $this->state([
            'dispensed_at' => now(),
            'dispensed_quantity' => fake()->randomFloat(2, 1, 50),
        ]);
    }
}
