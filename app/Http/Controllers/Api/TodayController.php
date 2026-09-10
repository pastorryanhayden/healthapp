<?php

namespace App\Http\Controllers\Api;

use App\Health\DayAssembler;
use App\Http\Controllers\Controller;
use App\Support\HealthClock;
use Illuminate\Http\JsonResponse;

class TodayController extends Controller
{
    public function __invoke(DayAssembler $days): JsonResponse
    {
        return response()->json($days->forDate(HealthClock::todayString()));
    }
}
