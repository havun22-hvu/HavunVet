<?php

namespace Database\Factories;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word() . 'cillin',
            'active_ingredient' => fake()->optional()->word(),
            'dosage_form' => fake()->randomElement(['tablet', 'injectie', 'zalf', 'druppels']),
            'strength' => fake()->optional()->numerify('## mg'),
            'stock_quantity' => fake()->randomFloat(2, 0, 100),
            'stock_unit' => 'stuks',
            'min_stock_level' => 10,
            'expiry_date' => fake()->dateTimeBetween('now', '+2 years'),
            'batch_number' => fake()->optional()->bothify('??-####'),
            'supplier' => fake()->optional()->company(),
            'purchase_price' => fake()->randomFloat(2, 1, 50),
            'selling_price' => fake()->randomFloat(2, 5, 100),
            'prescription_required' => fake()->boolean(),
            'notes' => null,
        ];
    }

    public function lowStock(): static
    {
        return $this->state([
            'stock_quantity' => 5,
            'min_stock_level' => 10,
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'expiry_date' => fake()->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    public function expiringSoon(): static
    {
        return $this->state([
            'expiry_date' => fake()->dateTimeBetween('+1 day', '+2 months'),
        ]);
    }
}
