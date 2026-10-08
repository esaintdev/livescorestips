<?php

namespace Database\Factories;

use App\Models\Prediction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Prediction>
 */
class PredictionFactory extends Factory
{
    protected $model = Prediction::class;

    public function definition(): array
    {
        return [
            'fixture_id' => $this->faker->unique()->numberBetween(100000, 999999),
            'league_id' => 39,
            'slug' => null,
            'league_name' => 'Premier League',
            'league_country' => 'England',
            'league_logo' => null,
            'home_team' => $this->faker->city().' FC',
            'home_team_logo' => null,
            'away_team' => $this->faker->city().' United',
            'away_team_logo' => null,
            'home_score' => null,
            'away_score' => null,
            'match_date' => now()->addDay()->startOfDay(),
            'match_time' => '15:00',
            'home_form' => ['W', 'D', 'L', 'W', 'W'],
            'away_form' => ['L', 'L', 'D', 'W', 'L'],
            'tip' => '1X',
            'category' => 'Double Chance',
            'odds' => 1.25,
            'probability' => 80,
            'bookmaker_name' => '1xBet',
            'status' => 'pending',
            'is_featured' => false,
            'is_banker' => false,
            'admin_note' => null,
            'content_updated_at' => now(),
        ];
    }

    public function slugged(?string $slug = null): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => $slug ?: Str::slug($attributes['home_team'].'-vs-'.$attributes['away_team']),
        ]);
    }

    public function finished(int $home = 1, int $away = 1): static
    {
        return $this->state(fn () => [
            'status' => 'won',
            'home_score' => $home,
            'away_score' => $away,
            'match_date' => now()->subDay()->startOfDay(),
        ]);
    }
}
