<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Performance extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'batting_runs',
        'batting_balls',
        'batting_fours',
        'batting_sixes',
        'batting_dismissal',
        'bowling_overs',
        'bowling_maidens',
        'bowling_runs',
        'bowling_wickets',
        'fielding_catches',
        'fielding_stumpings',
        'fielding_runouts',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    /**
     * Strike rate (runs per 100 balls). Returns null if no balls were faced —
     * a 0/0 division would be misleading on the UI.
     */
    public function strikeRate(): ?float
    {
        if ($this->batting_balls <= 0) {
            return null;
        }
        return round(($this->batting_runs / $this->batting_balls) * 100, 2);
    }

    /**
     * Bowling economy: runs conceded per over. Overs come in cricket
     * notation (4.3 = 4 overs and 3 balls), so we convert to balls first.
     */
    public function economy(): ?float
    {
        $balls = $this->bowlingBalls();
        if ($balls === 0) {
            return null;
        }
        return round(($this->bowling_runs / $balls) * 6, 2);
    }

    private function bowlingBalls(): int
    {
        if (! $this->bowling_overs) {
            return 0;
        }
        // "4.3" means 4 overs (24 balls) plus 3 balls = 27.
        $parts = explode('.', $this->bowling_overs);
        $overs = (int) ($parts[0] ?? 0);
        $extra = (int) ($parts[1] ?? 0);
        return ($overs * 6) + $extra;
    }
}
