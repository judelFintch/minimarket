<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'expense_category_id' => ExpenseCategory::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->sentence(),
            'amount' => $this->faker->randomFloat(2, 5, 500),
            'currency' => $this->faker->randomElement(['CDF', 'USD', 'EUR']),
            'incurred_at' => $this->faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d'),
            'receipt_path' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
