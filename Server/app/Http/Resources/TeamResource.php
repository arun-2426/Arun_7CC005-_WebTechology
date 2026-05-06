<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type,
            'home_ground' => $this->home_ground,
            'notes'       => $this->notes,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
