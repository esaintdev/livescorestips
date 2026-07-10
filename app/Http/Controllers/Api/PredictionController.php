<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Metadata;
use App\Models\Prediction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $categorySlug = $request->input('category');

        $query = Prediction::query()->whereDate('match_date', $date);

        if ($categorySlug) {
            $map = [
                'double-chance' => 'Double Chance',
                'over-1-5-goals' => 'Over 1.5 Goals',
                'over-2-5-goals' => 'Over 2.5 Goals',
                'btts-gg' => 'Both Teams To Score',
                'draws' => 'Draw',
                'home-wins' => 'Home Win',
                'away-wins' => 'Away Win',
                'correct-score' => 'Correct Score',
            ];
            if ($category = $map[$categorySlug] ?? null) {
                $query->where('category', $category);
            }
        }

        $results = $query->orderBy('match_date')->orderBy('match_time')->get();

        $metadata = Metadata::where('route_name', 'home')->pluck('value', 'key');

        return response()->json([
            'data' => $results->map(fn($p) => $this->toPrediction($p)),
            'date' => $date,
            'meta' => [
                'page_title' => $metadata['page_title'] ?? null,
                'page_description' => $metadata['page_description'] ?? null,
            ],
        ]);
    }

    public function byDate(string $date, Request $request): JsonResponse
    {
        return $this->index($request->merge(['date' => $date]));
    }

    public function live(): JsonResponse
    {
        $predictions = Prediction::liveWindow()->get();

        return response()->json([
            'data' => $predictions->map(fn($p) => $this->toPrediction($p)),
            'count' => $predictions->count(),
        ]);
    }

    private function toPrediction(Prediction $p): array
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
            'home_team' => [
                'name' => $p->home_team,
                'logo' => $p->home_team_logo ? url($p->home_team_logo) : null,
            ],
            'away_team' => [
                'name' => $p->away_team,
                'logo' => $p->away_team_logo ? url($p->away_team_logo) : null,
            ],
            'score' => [
                'home' => $p->home_score,
                'away' => $p->away_score,
            ],
            'match_date' => $p->match_date->format('Y-m-d'),
            'match_time' => Carbon::parse($p->match_time)->format('H:i'),
            'prediction' => [
                'tip' => $p->tip,
                'category' => $p->category,
                'odds' => $p->odds,
                'probability' => $p->probability,
            ],
            'status' => $p->status,
            'url' => route('fixture.show', [$p->slug ?? $p->fixture_id, $p->match_date->format('Y-m-d')]),
        ];
    }
}