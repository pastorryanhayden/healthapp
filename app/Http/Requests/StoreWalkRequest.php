<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalkRequest extends FormRequest
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
            'miles' => ['required', 'numeric', 'gt:0'],
            'date' => ['sometimes', 'date_format:Y-m-d'],
        ];
    }
}
