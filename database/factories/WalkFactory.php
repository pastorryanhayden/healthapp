<?php

namespace Database\Factories;

use App\Models\Walk;
use App\Support\HealthClock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Walk>
 */
class WalkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => HealthClock::todayString(),
            'miles' => fake()->randomFloat(2, 0.5, 3),
        ];
    }
}
