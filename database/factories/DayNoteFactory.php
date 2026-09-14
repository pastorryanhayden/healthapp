<?php

namespace Database\Factories;

use App\Models\DayNote;
use App\Support\HealthClock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DayNote>
 */
class DayNoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => HealthClock::todayString(),
            'body' => fake()->sentence(),
        ];
    }
}
