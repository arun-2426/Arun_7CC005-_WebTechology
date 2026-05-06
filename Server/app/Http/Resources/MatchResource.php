<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'match_date'     => $this->match_date?->toDateString(),
            'format'         => $this->format,
            'result'         => $this->result,
            'own_team_score' => $this->own_team_score,
            'opponent_score' => $this->opponent_score,
            'notes'          => $this->notes,

            // Related resources are eager-loaded by the controller — see
            // MatchController::index/show. whenLoaded() prevents accidental
            // N+1s when a caller forgets to load.
            'own_team'       => new TeamResource($this->whenLoaded('ownTeam')),
            'opponent_team'  => new TeamResource($this->whenLoaded('opponentTeam')),
            'venue'          => new VenueResource($this->whenLoaded('venue')),
            'performance'    => new PerformanceResource($this->whenLoaded('performance')),
            'photos'         => MatchPhotoResource::collection($this->whenLoaded('photos')),

            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
