<?php

namespace Database\Factories;

use App\Models\WeighIn;
use App\Support\HealthClock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeighIn>
 */
class WeighInFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => HealthClock::todayString(),
            'pounds' => fake()->randomFloat(2, 150, 220),
        ];
    }
}
