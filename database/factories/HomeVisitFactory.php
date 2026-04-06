<?php

namespace Database\Factories;

use App\Models\HomeVisit;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class HomeVisitFactory extends Factory
{
    protected $model = HomeVisit::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'treatment_id' => null,
            'appointment_id' => null,
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 weeks'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->numerify('####') . fake()->lexify('??'),
            'latitude' => null,
            'longitude' => null,
            'travel_distance_km' => fake()->optional()->randomFloat(1, 1, 50),
            'travel_time_minutes' => fake()->optional()->numberBetween(5, 60),
            'travel_cost' => null,
            'notes' => null,
            'status' => 'scheduled',
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'scheduled_at' => fake()->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }
}
