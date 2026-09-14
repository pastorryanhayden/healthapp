<?php

use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\DayController;
use App\Http\Controllers\Api\DayNoteController;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\Api\FoodLogController;
use App\Http\Controllers\Api\TodayController;
use App\Http\Controllers\Api\WalkController;
use App\Http\Controllers\Api\WeighInController;
use Illuminate\Support\Facades\Route;

Route::get('/today', TodayController::class);
Route::get('/days/{date}', DayController::class)->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
Route::put('/days/{date}/note', [DayNoteController::class, 'upsert'])->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
Route::get('/calendar', CalendarController::class);

Route::get('/foods', [FoodController::class, 'index']);
Route::post('/food-logs', [FoodLogController::class, 'store']);
Route::patch('/food-logs/{foodLog}', [FoodLogController::class, 'update']);
Route::delete('/food-logs/{foodLog}', [FoodLogController::class, 'destroy']);

Route::post('/walks', [WalkController::class, 'store']);
Route::patch('/walks/{walk}', [WalkController::class, 'update']);
Route::delete('/walks/{walk}', [WalkController::class, 'destroy']);

Route::get('/weigh-ins', [WeighInController::class, 'index']);
Route::post('/weigh-ins', [WeighInController::class, 'store']);
