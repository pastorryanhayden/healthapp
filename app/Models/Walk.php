<?php

namespace App\Models;

use Database\Factories\WalkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'miles'])]
class Walk extends Model
{
    /** @use HasFactory<WalkFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'miles' => 'decimal:2',
        ];
    }
}
