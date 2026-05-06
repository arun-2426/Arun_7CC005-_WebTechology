<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'batting_runs'      => $this->batting_runs,
            'batting_balls'     => $this->batting_balls,
            'batting_fours'     => $this->batting_fours,
            'batting_sixes'     => $this->batting_sixes,
            'batting_dismissal' => $this->batting_dismissal,
            'bowling_overs'     => $this->bowling_overs,
            'bowling_maidens'   => $this->bowling_maidens,
            'bowling_runs'      => $this->bowling_runs,
            'bowling_wickets'   => $this->bowling_wickets,
            'fielding_catches'  => $this->fielding_catches,
            'fielding_stumpings'=> $this->fielding_stumpings,
            'fielding_runouts'  => $this->fielding_runouts,
            // Computed fields — pre-calculated server-side so the SPA doesn't
            // have to know how cricket overs are structured.
            'strike_rate'       => $this->strikeRate(),
            'economy'           => $this->economy(),
        ];
    }
}
