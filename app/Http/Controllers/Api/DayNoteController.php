<?php

namespace App\Http\Controllers\Api;

use App\Health\UpsertDayNote;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertDayNoteRequest;
use App\Support\HealthClock;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class DayNoteController extends Controller
{
    public function upsert(UpsertDayNoteRequest $request, string $date, UpsertDayNote $upsert): JsonResponse
    {
        $parsed = CarbonImmutable::createFromFormat('Y-m-d', $date, HealthClock::timezone());

        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            abort(404);
        }

        return response()->json($upsert->handle(
            date: $date,
            note: $request->validated('note'),
        ));
    }
}
