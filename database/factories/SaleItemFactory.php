<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->randomFloat(2, 1, 100);
        $discountRate = $this->faker->randomFloat(2, 0, 10);
        $discountAmount = round(($quantity * $unitPrice) * ($discountRate / 100), 2);
        $lineTotal = ($quantity * $unitPrice) - $discountAmount;

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_rate' => $discountRate,
            'discount_amount' => $discountAmount,
            'line_total' => $lineTotal,
        ];
    }
}
