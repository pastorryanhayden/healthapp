<?php

namespace App\Http\Controllers;

use App\Health\UpsertDayNote;
use App\Http\Requests\UpsertDayNoteRequest;
use App\Support\HealthClock;
use Illuminate\Http\RedirectResponse;

class DayNoteWebController extends Controller
{
    public function store(UpsertDayNoteRequest $request, UpsertDayNote $upsert): RedirectResponse
    {
        $upsert->handle(
            date: HealthClock::todayString(),
            note: $request->validated('note'),
        );

        return redirect()->route('home');
    }
}
