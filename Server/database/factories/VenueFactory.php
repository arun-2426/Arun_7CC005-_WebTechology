<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'name'       => $this->faker->unique()->city() . ' Cricket Ground',
            'city'       => $this->faker->city(),
            'country'    => 'England',
            'pitch_type' => 'turf',
        ];
    }
}
