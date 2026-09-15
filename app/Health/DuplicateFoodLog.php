<?php

namespace App\Health;

use App\Models\FoodLog;

class DuplicateFoodLog
{
    public function __construct(private DayAssembler $days) {}

    /**
     * @return array{food_log: array<string, mixed>, day: array<string, mixed>}
     */
    public function handle(FoodLog $log): array
    {
        $date = $log->date->toDateString();

        $copy = FoodLog::query()->create([
            'food_id' => $log->food_id,
            'date' => $date,
            'calories' => $log->calories,
            'input' => $log->input,
        ]);

        return [
            'food_log' => $this->days->foodLogPayload($copy),
            'day' => $this->days->forDate($date),
        ];
    }
}
