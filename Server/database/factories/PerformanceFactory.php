<?php

namespace Database\Factories;

use App\Models\GameMatch;
use App\Models\Performance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Performance>
 */
class PerformanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'match_id'         => GameMatch::factory(),
            'batting_runs'     => $this->faker->numberBetween(0, 100),
            'batting_balls'    => $this->faker->numberBetween(0, 80),
            'batting_fours'    => $this->faker->numberBetween(0, 8),
            'batting_sixes'    => $this->faker->numberBetween(0, 4),
            'batting_dismissal'=> 'caught',
            'bowling_overs'    => null,
            'bowling_maidens'  => 0,
            'bowling_runs'     => 0,
            'bowling_wickets'  => 0,
            'fielding_catches' => 0,
            'fielding_stumpings' => 0,
            'fielding_runouts' => 0,
        ];
    }
}
