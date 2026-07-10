<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use Illuminate\Http\Request;

class FixtureSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json(['fixtures' => []]);
        }

        $predictions = Prediction::where(function ($q) use ($query) {
            $q->where('home_team', 'like', "%{$query}%")
                ->orWhere('away_team', 'like', "%{$query}%")
                ->orWhere('league_name', 'like', "%{$query}%");
        })
            ->whereDate('match_date', '>=', now()->subDays(7))
            ->orderBy('match_date', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($prediction) {
                return [
                    'id' => $prediction->id,
                    'slug' => $prediction->slug,
                    'home_team' => $prediction->home_team,
                    'away_team' => $prediction->away_team,
                    'league_name' => $prediction->league_name,
                    'match_date' => $prediction->match_date->format('Y-m-d'),
                    'match_time' => $prediction->match_time,
                    'url' => route('fixture.show', [$prediction->slug ?? $prediction->fixture_id, $prediction->match_date->format('Y-m-d')]),
                ];
            });

        return response()->json(['fixtures' => $predictions]);
    }
}
