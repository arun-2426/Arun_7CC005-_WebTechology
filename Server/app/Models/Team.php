<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'home_ground',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Matches where this team was the player's own side. We expose this
     * separately from `matchesAgainst()` because the SPA stats page needs
     * to know which is which.
     */
    public function matchesAsOwn(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'own_team_id');
    }

    public function matchesAsOpponent(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'opponent_team_id');
    }
}
