<?php

namespace App\Models;

use Database\Factories\WeighInFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'pounds'])]
class WeighIn extends Model
{
    /** @use HasFactory<WeighInFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'pounds' => 'decimal:2',
        ];
    }
}
