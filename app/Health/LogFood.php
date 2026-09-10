<?php

namespace App\Health;

use App\Ai\Agents\CalorieEstimator;
use App\Models\Food;
use App\Models\FoodLog;
use App\Support\HealthClock;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class LogFood
{
    public function __construct(private DayAssembler $days) {}

    /**
     * @return array{food_log: array<string, mixed>, day: array<string, mixed>}
     */
    public function handle(string $input, ?string $date = null): array
    {
        $normalized = Food::normalize($input);
        $date ??= HealthClock::todayString();

        $food = Food::query()->where('normalized_name', $normalized)->first();

        if ($food === null || $food->calories < 1) {
            $estimate = $this->estimate($input);

            $food = Food::query()->updateOrCreate(
                ['normalized_name' => $normalized],
                [
                    'name' => $estimate['name'],
                    'calories' => $estimate['calories'],
                ],
            );
        }

        $log = FoodLog::query()->create([
            'food_id' => $food->id,
            'date' => $date,
            'calories' => $food->calories,
            'input' => $input,
        ]);

        return [
            'food_log' => $this->days->foodLogPayload($log),
            'day' => $this->days->forDate($date),
        ];
    }

    /**
     * @return array{name: string, calories: int}
     */
    private function estimate(string $input): array
    {
        try {
            $response = (new CalorieEstimator)->prompt($input);
        } catch (Throwable $e) {
            throw new HttpException(502, 'Could not estimate calories.', $e);
        }

        $name = trim((string) ($response['name'] ?? ''));
        $calories = (int) ($response['calories'] ?? 0);

        if ($name === '' || $calories < 1) {
            throw new HttpException(502, 'Could not estimate calories.');
        }

        return [
            'name' => $name,
            'calories' => $calories,
        ];
    }
}
