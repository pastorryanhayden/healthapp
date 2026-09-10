<?php

namespace App\Models;

use Database\Factories\FoodLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['food_id', 'date', 'calories', 'input'])]
class FoodLog extends Model
{
    /** @use HasFactory<FoodLogFactory> */
    use HasFactory;

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'calories' => 'integer',
        ];
    }
}
