<?php

namespace App\Models;

use Database\Factories\FoodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'normalized_name', 'calories'])]
class Food extends Model
{
    /** @use HasFactory<FoodFactory> */
    use HasFactory;

    protected $table = 'foods';

    public function foodLogs(): HasMany
    {
        return $this->hasMany(FoodLog::class);
    }

    public static function normalize(string $input): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', trim($input)) ?? '';

        return mb_strtolower($collapsed);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'calories' => 'integer',
        ];
    }
}
