<?php

namespace Database\Factories;

use App\Models\Food;
use App\Models\FoodLog;
use App\Support\HealthClock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FoodLog>
 */
class FoodLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'food_id' => Food::factory(),
            'date' => HealthClock::todayString(),
            'calories' => fake()->numberBetween(50, 800),
            'input' => fake()->words(3, true),
        ];
    }
}
