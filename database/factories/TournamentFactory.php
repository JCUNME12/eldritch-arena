<?php

namespace Database\Factories;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tournament>
 */
class TournamentFactory extends Factory
{
    protected $model = Tournament::class;

    public function definition(): array
    {
        return [
            'organizer_id' => User::factory()->state(['type' => 'organizer']),
            'title' => fake()->sentence(4),
            'game' => fake()->randomElement(['Magic: The Gathering', 'Pokémon', 'Yu-Gi-Oh!']),
            'starts_at' => now()->addDays(7),
            'prize' => 'Boosters e troféu',
            'entry_fee' => 25,
            'slots' => 32,
            'location' => 'Arena Eldritch',
            'description' => fake()->paragraph(),
            'highlighted' => false,
            'status' => Tournament::STATUS_PUBLISHED,
        ];
    }
}
