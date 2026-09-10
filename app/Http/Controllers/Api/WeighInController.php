<?php

namespace App\Http\Controllers\Api;

use App\Health\UpsertWeighIn;
use App\Health\WeighInHistory;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWeighInRequest;
use Illuminate\Http\JsonResponse;

class WeighInController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(WeighInHistory::make());
    }

    public function store(StoreWeighInRequest $request, UpsertWeighIn $upsert): JsonResponse
    {
        $payload = $upsert->handle(
            pounds: (float) $request->validated('pounds'),
            date: $request->validated('date'),
        );

        return response()->json($payload, 201);
    }
}
