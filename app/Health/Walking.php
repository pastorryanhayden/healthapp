<?php

namespace App\Health;

use App\Support\HealthClock;
use Carbon\CarbonImmutable;

class Walking
{
    public static function status(string $date, float $miles): string
    {
        if (self::isRestDay($date)) {
            return 'rest';
        }

        return $miles >= (float) config('health.miles_goal') ? 'pass' : 'fail';
    }

    public static function isRestDay(string $date): bool
    {
        $parsed = CarbonImmutable::createFromFormat('Y-m-d', $date, HealthClock::timezone());

        return $parsed !== false
            && $parsed->format('Y-m-d') === $date
            && $parsed->isSunday();
    }
}
