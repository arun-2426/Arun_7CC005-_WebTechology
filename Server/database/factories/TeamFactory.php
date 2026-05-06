<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            // Faker can produce duplicates within a single test run, which
            // collides with our (user_id, name) unique key. unique() avoids it.
            'name' => $this->faker->unique()->company() . ' XI',
            'type' => $this->faker->randomElement(['own', 'opponent']),
            'home_ground' => $this->faker->optional()->streetName(),
            'notes' => null,
        ];
    }
}
