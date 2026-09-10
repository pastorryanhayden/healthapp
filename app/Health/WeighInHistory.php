<?php

namespace App\Health;

use App\Models\WeighIn;
use App\Support\HealthClock;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class WeighInHistory
{
    /**
     * @return array<string, mixed>
     */
    public static function make(): array
    {
        $weighIns = WeighIn::query()->orderByDesc('date')->get();
        $items = $weighIns->map(fn (WeighIn $weighIn) => self::payload($weighIn))->all();
        $series = $weighIns
            ->sortBy(fn (WeighIn $weighIn) => $weighIn->date->toDateString())
            ->values()
            ->map(fn (WeighIn $weighIn) => [
                'date' => $weighIn->date->toDateString(),
                'pounds' => round((float) $weighIn->pounds, 2),
            ])
            ->all();

        $thisFriday = self::mostRecentFriday(HealthClock::today());
        $lastFriday = $thisFriday->subWeek();

        $thisWeek = WeighIn::query()->whereDate('date', $thisFriday->toDateString())->first();
        $lastWeek = WeighIn::query()->whereDate('date', $lastFriday->toDateString())->first();

        $thisWeekPounds = $thisWeek ? round((float) $thisWeek->pounds, 2) : null;
        $lastWeekPounds = $lastWeek ? round((float) $lastWeek->pounds, 2) : null;
        $goal = round((float) config('health.pounds_goal'), 2);
        $latest = $weighIns->first();
        $remaining = $latest
            ? round((float) $latest->pounds - $goal, 2)
            : null;

        return [
            'data' => $items,
            'series' => $series,
            'goal_pounds' => $goal,
            'remaining_pounds' => $remaining,
            'this_week' => $thisWeekPounds,
            'last_week' => $lastWeekPounds,
            'delta' => ($thisWeekPounds !== null && $lastWeekPounds !== null)
                ? round($thisWeekPounds - $lastWeekPounds, 2)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function mostRecentFriday(CarbonImmutable $day): CarbonImmutable
    {
        $daysSinceFriday = ($day->dayOfWeek - CarbonInterface::FRIDAY + 7) % 7;

        return $day->subDays($daysSinceFriday);
    }

    public static function payload(WeighIn $weighIn): array
    {
        return [
            'id' => $weighIn->id,
            'date' => $weighIn->date->toDateString(),
            'pounds' => round((float) $weighIn->pounds, 2),
            'created_at' => $weighIn->created_at?->timezone(HealthClock::timezone())->toIso8601String(),
        ];
    }
}
