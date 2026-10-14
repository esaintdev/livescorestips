<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Metadata;
use App\Models\Prediction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private array $categoryMap = [
        'double-chance' => 'Double Chance',
        'over-1-5-goals' => 'Over 1.5 Goals',
        'over-2-5-goals' => 'Over 2.5 Goals',
        'btts-gg' => 'Both Teams To Score',
        'draws' => 'Draw',
        'home-wins' => 'Home Win',
        'away-wins' => 'Away Win',
        'correct-score' => 'Correct Score',
    ];

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => collect($this->categoryMap)->map(fn($name, $slug) => [
                'slug' => $slug,
                'name' => $name,
                'url' => route('category.show', ['categorySlug' => $slug]),
            ])->values(),
        ]);
    }

    public function show(string $slug, Request $request): JsonResponse
    {
        $category = $this->categoryMap[$slug] ?? null;
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $date = $request->input('date', now()->format('Y-m-d'));

        $predictions = Prediction::where('category', $category)
            ->whereDate('match_date', $date)
            ->orderBy('match_time')
            ->get();

        $metadata = Metadata::where('route_name', 'category:' . $slug)->pluck('value', 'key');

        return response()->json([
            'data' => $predictions->map(fn($p) => $this->toPrediction($p)),
            'category' => ['slug' => $slug, 'name' => $category],
            'date' => $date,
            'meta' => [
                'page_title' => $metadata['page_title'] ?? null,
                'page_description' => $metadata['page_description'] ?? null,
                'page_subheader' => $metadata['page_subheader'] ?? null,
            ],
        ]);
    }

    public function byDate(string $slug, string $date, Request $request): JsonResponse
    {
        return $this->show($slug, $request->merge(['date' => $date]));
    }

    private function toPrediction($p): array
    {
        return [
            'id' => $p->id,
            'fixture_id' => $p->fixture_id,
            'slug' => $p->slug,
            'league' => [
                'id' => $p->league_id,
                'name' => $p->league_name,
                'country' => $p->league_country,
                'logo' => $p->league_logo ? url($p->league_logo) : null,
            ],
            'home_team' => $p->home_team,
            'away_team' => $p->away_team,
            'home_team_logo' => $p->home_team_logo ? url($p->home_team_logo) : null,
            'away_team_logo' => $p->away_team_logo ? url($p->away_team_logo) : null,
            'home_score' => $p->home_score,
            'away_score' => $p->away_score,
            'match_date' => $p->match_date->format('Y-m-d'),
            'match_time' => $p->match_time,
            'tip' => $p->tip,
            'category' => $p->category,
            'odds' => $p->odds,
            'probability' => $p->probability,
            'status' => $p->status,
        ];
    }
}