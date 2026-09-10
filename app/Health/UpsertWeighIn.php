<?php

namespace App\Health;

use App\Models\WeighIn;
use App\Support\HealthClock;

class UpsertWeighIn
{
    /**
     * @return array{weigh_in: array<string, mixed>, history: array<string, mixed>}
     */
    public function handle(float $pounds, ?string $date = null): array
    {
        $date ??= HealthClock::todayString();

        $weighIn = WeighIn::query()->whereDate('date', $date)->first();

        if ($weighIn) {
            $weighIn->update(['pounds' => $pounds]);
        } else {
            $weighIn = WeighIn::query()->create([
                'date' => $date,
                'pounds' => $pounds,
            ]);
        }

        return [
            'weigh_in' => WeighInHistory::payload($weighIn),
            'history' => WeighInHistory::make(),
        ];
    }
}
