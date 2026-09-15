<?php

use App\Http\Controllers\DayNoteWebController;
use App\Http\Controllers\FoodLogWebController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WalkWebController;
use App\Http\Controllers\WeighInWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::post('/food-logs', [FoodLogWebController::class, 'store'])->name('food-logs.store');
Route::patch('/food-logs/{foodLog}', [FoodLogWebController::class, 'update'])->name('food-logs.update');
Route::post('/food-logs/{foodLog}/duplicate', [FoodLogWebController::class, 'duplicate'])->name('food-logs.duplicate');
Route::delete('/food-logs/{foodLog}', [FoodLogWebController::class, 'destroy'])->name('food-logs.destroy');
Route::post('/walks', [WalkWebController::class, 'store'])->name('walks.store');
Route::patch('/walks/{walk}', [WalkWebController::class, 'update'])->name('walks.update');
Route::delete('/walks/{walk}', [WalkWebController::class, 'destroy'])->name('walks.destroy');
Route::post('/weigh-ins', [WeighInWebController::class, 'store'])->name('weigh-ins.store');
Route::post('/day-notes', [DayNoteWebController::class, 'store'])->name('day-notes.store');
