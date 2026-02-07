<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockOut>
 */
class StockOutFactory extends Factory
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
            'reference' => 'OUT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
            'reason' => $this->faker->optional()->sentence(3),
            'total_quantity' => $this->faker->randomFloat(2, 1, 50),
            'occurred_at' => $this->faker->dateTimeBetween('-5 days', 'now')->format('Y-m-d'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
