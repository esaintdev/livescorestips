<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prediction;
use App\Models\Article;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Prediction Stats
        $totalPredictions = Prediction::count();
        $wonPredictions = Prediction::where('status', 'won')->count();
        $lostPredictions = Prediction::where('status', 'lost')->count();
        $pendingPredictions = Prediction::where('status', 'pending')->count();

        $completedPredictions = $wonPredictions + $lostPredictions;
        $winRate = $completedPredictions > 0 ? round(($wonPredictions / $completedPredictions) * 100, 1) : 0;

        // Article Stats
        $totalArticles = Article::count();

        // Visits
        $totalVisits = DB::table('site_visits')->count();
        $uniqueVisitors = DB::table('site_visits')->distinct('ip_address')->count('ip_address');

        // Recent Predictions (Last 5)
        $recentPredictions = Prediction::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalPredictions',
            'wonPredictions',
            'lostPredictions',
            'pendingPredictions',
            'winRate',
            'totalArticles',
            'totalVisits',
            'uniqueVisitors',
            'recentPredictions'
        ));
    }
}
