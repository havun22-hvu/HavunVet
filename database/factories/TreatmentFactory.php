<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Database\Eloquent\Factories\Factory;

class TreatmentFactory extends Factory
{
    protected $model = Treatment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'date' => fake()->dateTimeBetween('-6 months', 'now'),
            'complaint' => fake()->sentence(),
            'anamnesis' => fake()->optional()->paragraph(),
            'examination' => fake()->optional()->paragraph(),
            'diagnosis' => fake()->optional()->sentence(),
            'treatment_description' => fake()->optional()->paragraph(),
            'follow_up_needed' => false,
            'follow_up_date' => null,
            'veterinarian' => fake()->name(),
            'havunadmin_invoice_id' => null,
            'status' => 'draft',
        ];
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }

    public function invoiced(): static
    {
        return $this->state(['status' => 'invoiced']);
    }

    public function needsFollowUp(): static
    {
        return $this->state([
            'follow_up_needed' => true,
            'follow_up_date' => now()->addDays(3),
        ]);
    }
}
