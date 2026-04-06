<?php

namespace Database\Factories;

use App\Models\Treatment;
use App\Models\TreatmentItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class TreatmentItemFactory extends Factory
{
    protected $model = TreatmentItem::class;

    public function definition(): array
    {
        return [
            'treatment_id' => Treatment::factory(),
            'description' => fake()->sentence(3),
            'quantity' => fake()->randomFloat(2, 1, 10),
            'unit' => fake()->randomElement(['stuk', 'ml', 'gram']),
            'unit_price' => fake()->randomFloat(2, 5, 200),
            'vat_rate' => 21.00,
        ];
    }

    public function vatFree(): static
    {
        return $this->state(['vat_rate' => 0.00]);
    }
}
