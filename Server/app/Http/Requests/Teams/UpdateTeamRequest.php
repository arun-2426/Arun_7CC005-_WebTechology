<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The TeamPolicy has already verified ownership before we get here,
        // so an authenticated user with a valid route binding is fine.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes', 'required', 'string', 'min:2', 'max:120',
                Rule::unique('teams', 'name')
                    ->where(fn ($q) => $q->where('user_id', $this->user()->id))
                    ->ignore($this->route('team')),
            ],
            'type'        => ['sometimes', 'required', Rule::in(['own', 'opponent'])],
            'home_ground' => ['nullable', 'string', 'max:120'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }
}
