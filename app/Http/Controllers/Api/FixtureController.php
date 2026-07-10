<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prediction;
use Illuminate\Http\JsonResponse;

class FixtureController extends Controller
{
    public function upcoming(): JsonResponse
    {
        $fixtures = Prediction::whereDate('match_date', '>=', now())
            ->orderBy('match_date')
            ->orderBy('match_time')
            ->paginate(10);

        return response()->json([
            'data' => $fixtures->map(fn($f) => [
                'id' => $f->id,
                'fixture_id' => $f->fixture_id,
                'slug' => $f->slug,
                'league' => $f->league_name,
                'country' => $f->league_country,
                'home_team' => $f->home_team,
                'away_team' => $f->away_team,
                'match_date' => $f->match_date->format('Y-m-d'),
                'match_time' => $f->match_time,
                'url' => route('fixture.show', [$f->slug ?? $f->fixture_id, $f->match_date->format('Y-m-d')]),
            ]),
            'meta' => [
                'current_page' => $fixtures->currentPage(),
                'last_page' => $fixtures->lastPage(),
                'total' => $fixtures->total(),
            ],
        ]);
    }
}