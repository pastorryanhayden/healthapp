<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeighInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pounds' => ['required', 'numeric', 'gt:0'],
            'date' => ['sometimes', 'date_format:Y-m-d'],
        ];
    }
}
