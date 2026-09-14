<?php

namespace App\Health;

use App\Models\DayNote;
use App\Models\FoodLog;
use App\Models\Walk;
use App\Models\WeighIn;
use App\Support\HealthClock;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class CalendarAssembler
{
    /**
     * @return array{month: string, days: list<array<string, mixed>>}
     */
    public function forMonth(string $month): array
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            throw new InvalidArgumentException('Invalid month.');
        }

        $start = CarbonImmutable::createFromFormat('Y-m-d', $month.'-01', HealthClock::timezone());

        if ($start === false || $start->format('Y-m') !== $month) {
            throw new InvalidArgumentException('Invalid month.');
        }

        $start = $start->startOfMonth();
        $end = $start->endOfMonth();
        $caloriesGoal = (int) config('health.calories_goal');

        $eatenByDate = FoodLog::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('date, count(*) as logs, sum(calories) as calories')
            ->groupBy('date')
            ->get()
            ->keyBy(fn ($row) => CarbonImmutable::parse($row->date)->toDateString());

        $milesByDate = Walk::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('date, sum(miles) as miles')
            ->groupBy('date')
            ->get()
            ->keyBy(fn ($row) => CarbonImmutable::parse($row->date)->toDateString());

        $weighInDates = WeighIn::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString())
            ->all();

        $noteDates = DayNote::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString())
            ->all();

        $days = [];

        for ($day = $start; $day->lte($end); $day = $day->addDay()) {
            $key = $day->toDateString();
            $eaten = $eatenByDate->get($key);
            $miles = round((float) ($milesByDate->get($key)?->miles ?? 0), 2);
            $logCount = (int) ($eaten?->logs ?? 0);
            $calories = (int) ($eaten?->calories ?? 0);

            $days[] = [
                'date' => $key,
                'eating' => $logCount > 0 && $calories <= $caloriesGoal ? 'pass' : 'fail',
                'walking' => Walking::status($key, $miles),
                'weigh_in' => in_array($key, $weighInDates, true),
                'note' => in_array($key, $noteDates, true),
            ];
        }

        return [
            'month' => $month,
            'days' => $days,
        ];
    }
}
