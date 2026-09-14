<?php

namespace App\Health;

use App\Models\DayNote;

class UpsertDayNote
{
    public function __construct(private DayAssembler $days) {}

    /**
     * @return array{day: array<string, mixed>}
     */
    public function handle(string $date, ?string $note): array
    {
        $text = trim((string) $note);

        if ($text === '') {
            DayNote::query()->whereDate('date', $date)->delete();
        } else {
            $existing = DayNote::query()->whereDate('date', $date)->first();

            if ($existing) {
                $existing->update(['body' => $text]);
            } else {
                DayNote::query()->create([
                    'date' => $date,
                    'body' => $text,
                ]);
            }
        }

        return [
            'day' => $this->days->forDate($date),
        ];
    }
}
