<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApiFootballService;
use Illuminate\Http\Request;

class FixturesController extends Controller
{
    protected $apiService;

    public function __construct(ApiFootballService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request)
    {
        // Get date from request or default to today
        $date = $request->input('date', now()->format('Y-m-d'));

        // Calculate previous and next dates
        $previousDate = \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d');
        $nextDate = \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d');

        try {
            // Fetch fixtures for the selected date
            $fixtures = $this->apiService->fetchFixtures($date);

            // If fixtures is null or not an array, set to empty array
            if (!is_array($fixtures)) {
                $fixtures = [];
            }

            return view('admin.fixtures.index', compact('fixtures', 'date', 'previousDate', 'nextDate'));
        } catch (\Exception $e) {
            \Log::error('Error fetching fixtures', [
                'date' => $date,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('admin.fixtures.index', [
                'fixtures' => [],
                'date' => $date,
                'previousDate' => $previousDate,
                'nextDate' => $nextDate,
                'error' => 'Unable to fetch fixtures: ' . $e->getMessage()
            ]);
        }
    }

    public function refresh(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        // Clear the file cache for this date
        $cacheKey = "fixtures_{$date}";
        \Cache::forget($cacheKey);

        try {
            // Fetch fresh data
            $fixtures = $this->apiService->fetchFixtures($date);

            return response()->json([
                'success' => true,
                'message' => 'Fixtures refreshed successfully',
                'count' => count($fixtures ?? [])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh fixtures: ' . $e->getMessage()
            ], 500);
        }
    }
}
