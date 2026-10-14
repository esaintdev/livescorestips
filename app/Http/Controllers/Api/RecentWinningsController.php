<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Metadata;
use App\Models\Prediction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecentWinningsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);

        $winnings = Prediction::where('status', 'won')
            ->orderBy('match_date', 'desc')
            ->paginate($perPage);

        $metadata = Metadata::where('route_name', 'recent-winnings.index')->pluck('value', 'key');

        return response()->json([
            'data' => $winnings->through(fn ($p) => $this->toWinning($p)),
            'meta' => [
                'current_page' => $winnings->currentPage(),
                'last_page' => $winnings->lastPage(),
                'total' => $winnings->total(),
                'page_title' => $metadata['page_title'] ?? null,
                'page_subheader' => $metadata['page_subheader'] ?? null,
                'page_description' => $metadata['page_description'] ?? null,
            ],
        ]);
    }

    private function toWinning(Prediction $p): array
    {
        return [
            'id' => $p->id,
            'slug' => $p->slug,
            'league' => $p->league_name,
            'home_team' => $p->home_team,
            'home_team_logo' => $p->home_team_logo ? url($p->home_team_logo) : null,
            'away_team' => $p->away_team,
            'away_team_logo' => $p->away_team_logo ? url($p->away_team_logo) : null,
            'home_score' => $p->home_score,
            'away_score' => $p->away_score,
            'match_date' => $p->match_date ? $p->match_date->format('Y-m-d') : null,
            'tip' => $p->tip,
            'category' => $p->category,
            'odds' => $p->odds,
            'status' => $p->status,
        ];
    }
}
