<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'sku' => $this->faker->unique()->bothify('SKU-####'),
            'barcode' => $this->faker->unique()->ean13(),
            'unit' => $this->faker->randomElement(['piece', 'kg', 'litre']),
            'cost_price' => $this->faker->randomFloat(2, 1, 50),
            'sale_price' => $this->faker->randomFloat(2, 1, 100),
            'currency' => $this->faker->randomElement(['CDF', 'USD', 'EUR']),
            'min_stock' => $this->faker->numberBetween(0, 10),
            'reorder_qty' => $this->faker->numberBetween(0, 20),
        ];
    }
}
