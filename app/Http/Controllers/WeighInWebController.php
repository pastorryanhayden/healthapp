<?php

namespace App\Http\Controllers;

use App\Health\UpsertWeighIn;
use App\Http\Requests\StoreWeighInRequest;
use Illuminate\Http\RedirectResponse;

class WeighInWebController extends Controller
{
    public function store(StoreWeighInRequest $request, UpsertWeighIn $upsert): RedirectResponse
    {
        $upsert->handle(
            pounds: (float) $request->validated('pounds'),
            date: $request->validated('date'),
        );

        return redirect()->route('home');
    }
}
