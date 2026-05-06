<?php

namespace App\Http\Requests\Venues;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'min:2', 'max:160',
                Rule::unique('venues', 'name')->where(
                    fn ($q) => $q->where('user_id', $this->user()->id)
                ),
            ],
            'city'       => ['nullable', 'string', 'max:100'],
            'country'    => ['nullable', 'string', 'max:100'],
            'pitch_type' => ['nullable', Rule::in(['turf', 'matting', 'astro', 'concrete', 'other'])],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ];
    }
}
