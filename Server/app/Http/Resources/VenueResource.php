<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'city'       => $this->city,
            'country'    => $this->country,
            'pitch_type' => $this->pitch_type,
            'notes'      => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
