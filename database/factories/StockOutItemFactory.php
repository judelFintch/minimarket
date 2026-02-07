<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockOut;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockOutItem>
 */
class StockOutItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_out_id' => StockOut::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->randomFloat(2, 1, 10),
        ];
    }
}
