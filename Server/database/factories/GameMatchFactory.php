<?php

namespace Database\Factories;

use App\Models\GameMatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameMatch>
 */
class GameMatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'match_date' => $this->faker->dateTimeBetween('-1 year', 'today')->format('Y-m-d'),
            'format'     => $this->faker->randomElement(['T20', 'ODI', 'Test', 'Club']),
            'result'     => $this->faker->randomElement(['won', 'lost', 'drawn']),
            'own_team_score' => $this->faker->numberBetween(80, 350) . '/' . $this->faker->numberBetween(0, 10),
            'opponent_score' => $this->faker->numberBetween(80, 350) . '/' . $this->faker->numberBetween(0, 10),
        ];
    }
}
