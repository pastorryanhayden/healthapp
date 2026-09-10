<?php

namespace App\Http\Controllers\Api;

use App\Health\CalendarAssembler;
use App\Http\Controllers\Controller;
use App\Support\HealthClock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CalendarController extends Controller
{
    public function __invoke(Request $request, CalendarAssembler $calendar): JsonResponse
    {
        $month = $request->query('month', HealthClock::now()->format('Y-m'));

        if (! is_string($month)) {
            abort(422, 'Invalid month.');
        }

        try {
            return response()->json($calendar->forMonth($month));
        } catch (InvalidArgumentException) {
            abort(422, 'Invalid month.');
        }
    }
}
