<?php

namespace App\Http\Controllers\Api;

use App\Health\DayAssembler;
use App\Health\LogWalk;
use App\Health\UpdateWalk;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWalkRequest;
use App\Http\Requests\UpdateWalkRequest;
use App\Models\Walk;
use Illuminate\Http\JsonResponse;

class WalkController extends Controller
{
    public function store(StoreWalkRequest $request, LogWalk $logWalk): JsonResponse
    {
        $payload = $logWalk->handle(
            miles: (float) $request->validated('miles'),
            date: $request->validated('date'),
        );

        return response()->json($payload, 201);
    }

    public function update(UpdateWalkRequest $request, Walk $walk, UpdateWalk $updateWalk): JsonResponse
    {
        return response()->json($updateWalk->handle(
            walk: $walk,
            miles: (float) $request->validated('miles'),
        ));
    }

    public function destroy(Walk $walk, DayAssembler $days): JsonResponse
    {
        $date = $walk->date->toDateString();
        $walk->delete();

        return response()->json([
            'day' => $days->forDate($date),
        ]);
    }
}
