<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 10, 200);
        $discountRate = $this->faker->randomFloat(2, 0, 10);
        $discountAmount = round($subtotal * ($discountRate / 100), 2);
        $taxRate = $this->faker->randomFloat(2, 0, 16);
        $taxAmount = round(($subtotal - $discountAmount) * ($taxRate / 100), 2);
        $total = round($subtotal - $discountAmount + $taxAmount, 2);

        return [
            'user_id' => User::factory(),
            'reference' => 'SALE-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'customer_name' => $this->faker->optional()->name(),
            'total_amount' => $total,
            'subtotal_amount' => $subtotal,
            'discount_rate' => $discountRate,
            'discount_amount' => $discountAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'status' => $this->faker->randomElement(['paid', 'pending']),
            'sold_at' => $this->faker->dateTimeBetween('-10 days', 'now'),
        ];
    }
}
