<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use Illuminate\Http\Request;

class RecentWinningsController extends Controller
{
    /**
     * Display the recent winnings page with all won predictions.
     */
    public function index()
    {
        $winnings = Prediction::where('status', 'won')
            ->orderBy('match_date', 'desc')
            ->paginate(20);

        $metadata = \App\Models\Metadata::where('route_name', 'recent-winnings.index')->pluck('value', 'key');

        return view('recent-winnings.index', compact('winnings', 'metadata'));
    }

    /**
     * Get recent winnings for homepage preview (limited).
     */
    public static function getRecentWinnings($limit = 6)
    {
        return Prediction::where('status', 'won')
            ->orderBy('match_date', 'desc')
            ->limit($limit)
            ->get();
    }
}
