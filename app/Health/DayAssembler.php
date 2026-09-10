<?php

namespace App\Health;

use App\Models\FoodLog;
use App\Models\Walk;
use App\Support\HealthClock;
use Illuminate\Support\Collection;

class DayAssembler
{
    /**
     * @return array<string, mixed>
     */
    public function forDate(string $date): array
    {
        $logs = FoodLog::query()
            ->with('food')
            ->whereDate('date', $date)
            ->orderBy('id')
            ->get();

        $walks = Walk::query()
            ->whereDate('date', $date)
            ->orderBy('id')
            ->get();

        $caloriesGoal = (int) config('health.calories_goal');
        $milesGoal = (float) config('health.miles_goal');
        $eaten = (int) $logs->sum('calories');
        $miles = round((float) $walks->sum('miles'), 2);

        return [
            'date' => $date,
            'calories_eaten' => $eaten,
            'calories_goal' => $caloriesGoal,
            'calories_remaining' => $caloriesGoal - $eaten,
            'eating' => $logs->isNotEmpty() && $eaten <= $caloriesGoal ? 'pass' : 'fail',
            'miles_walked' => $miles,
            'miles_goal' => $milesGoal,
            'walking' => $miles >= $milesGoal ? 'pass' : 'fail',
            'food_logs' => $this->foodLogs($logs),
            'walks' => $this->walks($walks),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function foodLogPayload(FoodLog $log): array
    {
        $log->loadMissing('food');

        return [
            'id' => $log->id,
            'input' => $log->input,
            'name' => $log->food->name,
            'calories' => $log->calories,
            'created_at' => $log->created_at?->timezone(HealthClock::timezone())->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function walkPayload(Walk $walk): array
    {
        return [
            'id' => $walk->id,
            'miles' => round((float) $walk->miles, 2),
            'created_at' => $walk->created_at?->timezone(HealthClock::timezone())->toIso8601String(),
        ];
    }

    /**
     * @param  Collection<int, FoodLog>  $logs
     * @return list<array<string, mixed>>
     */
    private function foodLogs(Collection $logs): array
    {
        return $logs->map(fn (FoodLog $log) => $this->foodLogPayload($log))->all();
    }

    /**
     * @param  Collection<int, Walk>  $walks
     * @return list<array<string, mixed>>
     */
    private function walks(Collection $walks): array
    {
        return $walks->map(fn (Walk $walk) => $this->walkPayload($walk))->all();
    }
}
