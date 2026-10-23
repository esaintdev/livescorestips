<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiFootballService;
use Carbon\Carbon;

class LeagueController extends Controller
{
    protected $apiFootballService;

    public function __construct(ApiFootballService $apiFootballService)
    {
        $this->apiFootballService = $apiFootballService;
    }

    public function show($id, $slug = null)
    {
        $league = $this->apiFootballService->getLeagueById($id);

        if (!$league) {
            abort(404);
        }

        // One canonical URL per league: any other slug 301s to it
        $canonicalSlug = $league['slug'] ?? \Illuminate\Support\Str::slug($league['name']);
        if ($slug !== $canonicalSlug) {
            return redirect()->route('league.show', ['id' => $league['id'], 'slug' => $canonicalSlug], 301);
        }

        $season = now()->month >= 7 ? now()->year : now()->year - 1;

        $standings = $this->apiFootballService->fetchLeagueStandings($id, $season);
        $results = $this->apiFootballService->fetchLeagueResults($id, 20);
        $upcoming = $this->apiFootballService->fetchLeagueUpcomingFixtures($id, 20);

        // Fetch ALL predictions for this league — no date filter, no limit
        $predictions = \App\Models\Prediction::where('league_id', $id)
            ->orderBy('match_date', 'asc')
            ->get();

        return view('league.show', compact('league', 'standings', 'results', 'upcoming', 'predictions', 'season'));
    }
}
