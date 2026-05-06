<?php

namespace Tests\Feature;

use App\Models\GameMatch;
use App\Models\Performance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_returns_aggregated_numbers(): void
    {
        $user = User::factory()->create();

        $m1 = GameMatch::factory()->for($user)->create(['result' => 'won']);
        $m2 = GameMatch::factory()->for($user)->create(['result' => 'lost']);
        $m3 = GameMatch::factory()->for($user)->create(['result' => 'won']);

        Performance::factory()->for($m1, 'match')->create([
            'batting_runs' => 50, 'batting_balls' => 40, 'batting_dismissal' => 'caught',
        ]);
        Performance::factory()->for($m2, 'match')->create([
            'batting_runs' => 0, 'batting_balls' => 5, 'batting_dismissal' => 'bowled',
        ]);
        Performance::factory()->for($m3, 'match')->create([
            'batting_runs' => 30, 'batting_balls' => 20, 'batting_dismissal' => 'not_out',
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/stats/summary')
                         ->assertOk();

        $response->assertJsonPath('totals.matches_total', 3)
                 ->assertJsonPath('totals.wins', 2)
                 ->assertJsonPath('totals.losses', 1)
                 ->assertJsonPath('batting.total_runs', 80)
                 ->assertJsonPath('batting.times_out', 2)
                 // average = 80 runs / 2 dismissals = 40
                 ->assertJsonPath('batting.average', 40);
    }

    public function test_summary_handles_user_with_no_matches(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/stats/summary')
                         ->assertOk();

        $response->assertJsonPath('totals.matches_total', 0)
                 ->assertJsonPath('batting.average', null);
    }
}
