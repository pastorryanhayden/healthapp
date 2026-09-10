<?php

namespace App\Http\Controllers;

use App\Health\LogFood;
use App\Health\UpdateFoodLog;
use App\Http\Requests\StoreFoodLogRequest;
use App\Http\Requests\UpdateFoodLogRequest;
use App\Models\FoodLog;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FoodLogWebController extends Controller
{
    public function store(StoreFoodLogRequest $request, LogFood $logFood): RedirectResponse
    {
        try {
            $logFood->handle(
                input: trim((string) $request->validated('input')),
                date: $request->validated('date'),
            );
        } catch (HttpException $e) {
            if ($e->getStatusCode() === 502) {
                return back()->withErrors(['input' => 'Could not estimate calories.']);
            }

            throw $e;
        }

        return redirect()->route('home');
    }

    public function update(UpdateFoodLogRequest $request, FoodLog $foodLog, UpdateFoodLog $updateFoodLog): RedirectResponse
    {
        $name = $request->validated('name');

        $updateFoodLog->handle(
            log: $foodLog,
            calories: (int) $request->validated('calories'),
            name: is_string($name) ? trim($name) : null,
        );

        return redirect()->route('home');
    }

    public function destroy(FoodLog $foodLog): RedirectResponse
    {
        $foodLog->delete();

        return redirect()->route('home');
    }
}
