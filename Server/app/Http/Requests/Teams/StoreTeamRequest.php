<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Auth middleware has already gated this route. We don't need an
        // additional check — anyone logged in can add their own teams.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'min:2', 'max:120',
                // Names are unique per-user, not globally. Two users can both
                // have a team called "Wolverhampton XI".
                Rule::unique('teams', 'name')->where(
                    fn ($q) => $q->where('user_id', $this->user()->id)
                ),
            ],
            'type'        => ['required', Rule::in(['own', 'opponent'])],
            'home_ground' => ['nullable', 'string', 'max:120'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }
}
