<?php

namespace App\Health;

use App\Models\Walk;

class UpdateWalk
{
    public function __construct(private DayAssembler $days) {}

    /**
     * @return array{walk: array<string, mixed>, day: array<string, mixed>}
     */
    public function handle(Walk $walk, float $miles): array
    {
        $walk->update(['miles' => $miles]);

        return [
            'walk' => $this->days->walkPayload($walk->fresh()),
            'day' => $this->days->forDate($walk->date->toDateString()),
        ];
    }
}
