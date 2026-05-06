<?php

namespace App\Http\Requests\Matches;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        // FK targets must belong to the same user — otherwise a malicious
        // client could attach their match to someone else's team.
        $teamExistsForUser = Rule::exists('teams', 'id')
            ->where(fn ($q) => $q->where('user_id', $userId));

        $venueExistsForUser = Rule::exists('venues', 'id')
            ->where(fn ($q) => $q->where('user_id', $userId));

        return [
            'match_date'        => ['required', 'date', 'before_or_equal:today'],
            'format'            => ['required', Rule::in(['T20', 'ODI', 'Test', 'Club', 'Other'])],
            'own_team_id'       => ['nullable', 'integer', $teamExistsForUser],
            'opponent_team_id'  => ['nullable', 'integer', $teamExistsForUser, 'different:own_team_id'],
            'venue_id'          => ['nullable', 'integer', $venueExistsForUser],
            'result'            => ['nullable', Rule::in(['won', 'lost', 'drawn', 'tied', 'no_result'])],
            'own_team_score'    => ['nullable', 'string', 'max:40'],
            'opponent_score'    => ['nullable', 'string', 'max:40'],
            'notes'             => ['nullable', 'string', 'max:5000'],

            // Performance is nested — sent in the same payload to keep the
            // SPA flow as one form rather than a chained-call dance.
            'performance'                       => ['nullable', 'array'],
            'performance.batting_runs'          => ['nullable', 'integer', 'min:0', 'max:600'],
            'performance.batting_balls'         => ['nullable', 'integer', 'min:0', 'max:1000'],
            'performance.batting_fours'         => ['nullable', 'integer', 'min:0', 'max:100'],
            'performance.batting_sixes'         => ['nullable', 'integer', 'min:0', 'max:100'],
            'performance.batting_dismissal'     => [
                'nullable',
                Rule::in(['not_out', 'bowled', 'caught', 'lbw', 'run_out',
                    'stumped', 'hit_wicket', 'retired', 'did_not_bat']),
            ],
            'performance.bowling_overs'         => ['nullable', 'string', 'regex:/^\d+(\.[0-5])?$/'],
            'performance.bowling_maidens'       => ['nullable', 'integer', 'min:0', 'max:50'],
            'performance.bowling_runs'          => ['nullable', 'integer', 'min:0', 'max:500'],
            'performance.bowling_wickets'       => ['nullable', 'integer', 'min:0', 'max:10'],
            'performance.fielding_catches'      => ['nullable', 'integer', 'min:0', 'max:20'],
            'performance.fielding_stumpings'    => ['nullable', 'integer', 'min:0', 'max:20'],
            'performance.fielding_runouts'      => ['nullable', 'integer', 'min:0', 'max:20'],
        ];
    }
}
