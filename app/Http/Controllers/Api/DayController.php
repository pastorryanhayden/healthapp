<?php

namespace App\Http\Controllers\Api;

use App\Health\DayAssembler;
use App\Http\Controllers\Controller;
use App\Support\HealthClock;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class DayController extends Controller
{
    public function __invoke(string $date, DayAssembler $days): JsonResponse
    {
        $parsed = CarbonImmutable::createFromFormat('Y-m-d', $date, HealthClock::timezone());

        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            abort(404);
        }

        return response()->json($days->forDate($date));
    }
}
