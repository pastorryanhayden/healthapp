<?php

namespace App\Http\Controllers;

use App\Health\LogWalk;
use App\Health\UpdateWalk;
use App\Http\Requests\StoreWalkRequest;
use App\Http\Requests\UpdateWalkRequest;
use App\Models\Walk;
use Illuminate\Http\RedirectResponse;

class WalkWebController extends Controller
{
    public function store(StoreWalkRequest $request, LogWalk $logWalk): RedirectResponse
    {
        $logWalk->handle(
            miles: (float) $request->validated('miles'),
            date: $request->validated('date'),
        );

        return redirect()->route('home');
    }

    public function update(UpdateWalkRequest $request, Walk $walk, UpdateWalk $updateWalk): RedirectResponse
    {
        $updateWalk->handle(
            walk: $walk,
            miles: (float) $request->validated('miles'),
        );

        return redirect()->route('home');
    }

    public function destroy(Walk $walk): RedirectResponse
    {
        $walk->delete();

        return redirect()->route('home');
    }
}
