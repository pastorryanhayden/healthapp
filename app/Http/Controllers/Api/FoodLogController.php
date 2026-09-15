<?php

namespace App\Http\Controllers\Api;

use App\Health\DayAssembler;
use App\Health\DuplicateFoodLog;
use App\Health\LogFood;
use App\Health\UpdateFoodLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodLogRequest;
use App\Http\Requests\UpdateFoodLogRequest;
use App\Models\FoodLog;
use Illuminate\Http\JsonResponse;

class FoodLogController extends Controller
{
    public function store(StoreFoodLogRequest $request, LogFood $logFood): JsonResponse
    {
        $payload = $logFood->handle(
            input: trim((string) $request->validated('input')),
            date: $request->validated('date'),
        );

        return response()->json($payload, 201);
    }

    public function update(UpdateFoodLogRequest $request, FoodLog $foodLog, UpdateFoodLog $updateFoodLog): JsonResponse
    {
        $name = $request->validated('name');

        return response()->json($updateFoodLog->handle(
            log: $foodLog,
            calories: (int) $request->validated('calories'),
            name: is_string($name) ? trim($name) : null,
        ));
    }

    public function destroy(FoodLog $foodLog, DayAssembler $days): JsonResponse
    {
        $date = $foodLog->date->toDateString();
        $foodLog->delete();

        return response()->json([
            'day' => $days->forDate($date),
        ]);
    }

    public function duplicate(FoodLog $foodLog, DuplicateFoodLog $duplicate): JsonResponse
    {
        return response()->json($duplicate->handle($foodLog), 201);
    }
}
