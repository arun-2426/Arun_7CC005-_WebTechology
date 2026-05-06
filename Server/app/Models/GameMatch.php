<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class is named GameMatch because `Match` is a reserved word in PHP 8.
 * The DB table is still `matches`.
 */
class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'user_id',
        'match_date',
        'format',
        'own_team_id',
        'opponent_team_id',
        'venue_id',
        'result',
        'own_team_score',
        'opponent_score',
        'notes',
    ];

    protected $casts = [
        'match_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ownTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'own_team_id');
    }

    public function opponentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'opponent_team_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function performance(): HasOne
    {
        return $this->hasOne(Performance::class, 'match_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(MatchPhoto::class, 'match_id');
    }
}
