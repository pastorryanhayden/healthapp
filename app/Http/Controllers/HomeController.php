<?php

namespace App\Http\Controllers;

use App\Health\CalendarAssembler;
use App\Health\DayAssembler;
use App\Health\WeighInHistory;
use App\Support\HealthClock;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(DayAssembler $days, CalendarAssembler $calendar): View
    {
        $today = HealthClock::todayString();

        return view('home', [
            'day' => $days->forDate($today),
            'calendar' => $calendar->forMonth(HealthClock::now()->format('Y-m')),
            'weighIns' => WeighInHistory::make(),
        ]);
    }
}
