<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 weeks'),
            'duration_minutes' => 30,
            'type' => 'consult',
            'status' => 'scheduled',
            'reason' => fake()->optional()->sentence(),
            'notes' => null,
            'contact_phone' => fake()->optional()->phoneNumber(),
        ];
    }

    public function today(): static
    {
        return $this->state([
            'scheduled_at' => now()->setTime(fake()->numberBetween(8, 17), 0),
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'scheduled_at' => fake()->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }
}
