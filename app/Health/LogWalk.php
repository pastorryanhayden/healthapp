<?php

namespace App\Health;

use App\Models\Walk;
use App\Support\HealthClock;

class LogWalk
{
    public function __construct(private DayAssembler $days) {}

    /**
     * @return array{walk: array<string, mixed>, day: array<string, mixed>}
     */
    public function handle(float $miles, ?string $date = null): array
    {
        $date ??= HealthClock::todayString();

        $walk = Walk::query()->create([
            'date' => $date,
            'miles' => $miles,
        ]);

        return [
            'walk' => $this->days->walkPayload($walk),
            'day' => $this->days->forDate($date),
        ];
    }
}
