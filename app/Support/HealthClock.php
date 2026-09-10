<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class HealthClock
{
    public static function timezone(): string
    {
        return (string) config('health.timezone');
    }

    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::timezone());
    }

    public static function today(): CarbonImmutable
    {
        return self::now()->startOfDay();
    }

    public static function todayString(): string
    {
        return self::today()->toDateString();
    }
}
