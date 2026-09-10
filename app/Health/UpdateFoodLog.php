<?php

namespace App\Health;

use App\Models\FoodLog;

class UpdateFoodLog
{
    public function __construct(private DayAssembler $days) {}

    /**
     * @return array{food_log: array<string, mixed>, day: array<string, mixed>}
     */
    public function handle(FoodLog $log, int $calories, ?string $name = null): array
    {
        $log->update(['calories' => $calories]);

        $foodAttributes = ['calories' => $calories];
        if ($name !== null && $name !== '') {
            $foodAttributes['name'] = $name;
        }

        $log->food->update($foodAttributes);

        return [
            'food_log' => $this->days->foodLogPayload($log->fresh('food')),
            'day' => $this->days->forDate($log->date->toDateString()),
        ];
    }
}
