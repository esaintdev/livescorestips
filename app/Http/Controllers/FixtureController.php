<?php

namespace App\Http\Controllers;

use App\Services\ApiFootballService;
use Illuminate\Http\Request;

class FixtureController extends Controller
{
    protected $apiService;

    public function __construct(ApiFootballService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function show($slug, $date)
    {
        try {
            // Try to find prediction by slug first, fallback to fixture_id
            $prediction = \App\Models\Prediction::where('slug', $slug)
                ->orWhere('fixture_id', $slug)
                ->orWhere('id', $slug)
                ->first();

            // If found by old ID, redirect to slug URL
            if ($prediction && $prediction->slug && $slug != $prediction->slug) {
                return redirect()->route('fixture.show', [$prediction->slug, $prediction->match_date->format('Y-m-d')], 301);
            }

            // Get fixture ID for API calls
            $fixtureId = $prediction ? $prediction->fixture_id : $slug;

            // Fetch fixture details
            $fixture = $this->apiService->fetchFixtureDetails($fixtureId);

            if (!$fixture) {
                return redirect()->route('home')->with('error', 'Fixture not found. It may have been removed or the ID is invalid.');
            }

            // Extract team and league info
            $homeTeamId = $fixture['teams']['home']['id'];
            $awayTeamId = $fixture['teams']['away']['id'];
            $leagueId = $fixture['league']['id'];
            $season = $fixture['league']['season'];

            // Fetch all related data (with error handling for each)
            try {
                $odds = $this->apiService->fetchOdds($fixtureId);
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch odds', ['fixture_id' => $fixtureId, 'error' => $e->getMessage()]);
                $odds = [];
            }

            try {
                $homeForm = $this->apiService->fetchTeamForm($homeTeamId, 5);
                $awayForm = $this->apiService->fetchTeamForm($awayTeamId, 5);
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch team forms', ['error' => $e->getMessage()]);
                $homeForm = [];
                $awayForm = [];
            }

            try {
                $headToHead = $this->apiService->fetchHeadToHead($homeTeamId, $awayTeamId, 5);
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch head-to-head', ['error' => $e->getMessage()]);
                $headToHead = [];
            }

            try {
                $allGroups = $this->apiService->fetchLeagueStandings($leagueId, $season, true);
                $standings = [];
                foreach ($allGroups as $group) {
                    foreach ($group as $team) {
                        if ($team['team']['id'] == $homeTeamId || $team['team']['id'] == $awayTeamId) {
                            $standings = $group;
                            break 2;
                        }
                    }
                }
                if (empty($standings) && !empty($allGroups)) {
                    $standings = $allGroups[0];
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch standings', ['error' => $e->getMessage()]);
                $standings = [];
            }

            try {
                $homeStats = $this->apiService->fetchTeamStatistics($homeTeamId, $leagueId, $season);
                $awayStats = $this->apiService->fetchTeamStatistics($awayTeamId, $leagueId, $season);
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch team statistics', ['error' => $e->getMessage()]);
                $homeStats = null;
                $awayStats = null;
            }

            // Fetch comments for this prediction
            $comments = $prediction ? $prediction->comments()->approved()->latest()->get() : collect();

            return view('fixture.show', compact(
                'fixture',
                'prediction',
                'odds',
                'homeForm',
                'awayForm',
                'headToHead',
                'standings',
                'homeStats',
                'awayStats',
                'comments'
            ));
        } catch (\Exception $e) {
            \Log::error('Error loading fixture details', [
                'fixture_id' => $fixtureId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('home')->with('error', 'Unable to load fixture details. Please try again later.');
        } finally {
            // Explicitly disconnect to free up connection after API-heavy operations
            \DB::disconnect();
        }
    }
}
