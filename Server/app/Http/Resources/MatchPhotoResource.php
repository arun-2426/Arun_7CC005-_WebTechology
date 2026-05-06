<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'caption'    => $this->caption,
            'url'        => $this->url,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
