<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpensePayment>
 */
class ExpensePaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_id' => Expense::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 300),
            'payment_method' => $this->faker->randomElement(['cash', 'mobile', 'card', 'bank']),
            'paid_at' => $this->faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
