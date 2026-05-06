<?php

namespace Tests\Feature;

use App\Models\GameMatch;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_match_create_with_nested_performance(): void
    {
        $user = User::factory()->create();
        $own  = Team::factory()->for($user)->create(['type' => 'own']);
        $opp  = Team::factory()->for($user)->create(['type' => 'opponent']);
        $venue = Venue::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/matches', [
            'match_date'       => '2026-04-01',
            'format'           => 'T20',
            'own_team_id'      => $own->id,
            'opponent_team_id' => $opp->id,
            'venue_id'         => $venue->id,
            'result'           => 'won',
            'own_team_score'   => '184/6',
            'opponent_score'   => '160/9',
            'performance'      => [
                'batting_runs'      => 64,
                'batting_balls'     => 41,
                'batting_fours'     => 7,
                'batting_sixes'     => 2,
                'batting_dismissal' => 'caught',
            ],
        ]);

        $response->assertCreated()
                 ->assertJsonPath('data.format', 'T20')
                 ->assertJsonPath('data.performance.batting_runs', 64);

        $this->assertDatabaseHas('performances', ['batting_runs' => 64]);
    }

    public function test_match_validation_blocks_same_team_both_sides(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
             ->postJson('/api/matches', [
                 'match_date'       => '2026-04-01',
                 'format'           => 'T20',
                 'own_team_id'      => $team->id,
                 'opponent_team_id' => $team->id,
             ])
             ->assertStatus(422)
             ->assertJsonValidationErrors('opponent_team_id');
    }

    public function test_index_filters_by_format(): void
    {
        $user = User::factory()->create();
        GameMatch::factory()->for($user)->create(['format' => 'T20']);
        GameMatch::factory()->for($user)->create(['format' => 'ODI']);
        GameMatch::factory()->for($user)->create(['format' => 'T20']);

        $this->actingAs($user, 'sanctum')
             ->getJson('/api/matches?format=T20')
             ->assertOk()
             ->assertJsonCount(2, 'data');
    }

    public function test_user_cannot_update_anothers_match(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        $alicesMatch = GameMatch::factory()->for($alice)->create();

        $this->actingAs($bob, 'sanctum')
             ->putJson("/api/matches/{$alicesMatch->id}", ['format' => 'ODI'])
             ->assertNotFound();
    }
}
