<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\JsonResponse;

class FoodController extends Controller
{
    public function index(): JsonResponse
    {
        $foods = Food::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Food $food) => [
                'id' => $food->id,
                'name' => $food->name,
                'normalized_name' => $food->normalized_name,
                'calories' => $food->calories,
            ])
            ->all();

        return response()->json(['data' => $foods]);
    }
}
