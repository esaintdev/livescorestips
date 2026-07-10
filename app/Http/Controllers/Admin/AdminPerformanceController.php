<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prediction;
use Carbon\Carbon;

class AdminPerformanceController extends Controller
{
    public function index()
    {
        $predictions = Prediction::whereIn('status', ['won', 'lost'])
            ->orderBy('match_date', 'desc')
            ->get();

        // Overall Stats
        $totalPredictions = Prediction::count();
        $wonPredictions = Prediction::where('status', 'won')->count();
        $lostPredictions = Prediction::where('status', 'lost')->count();
        $pendingPredictions = Prediction::where('status', 'pending')->count();
        $overallAccuracy = $totalPredictions > 0 ? round((($wonPredictions + $lostPredictions) > 0 ? ($wonPredictions / ($wonPredictions + $lostPredictions)) * 100 : 0), 1) : 0;

        $overallStats = [
            'total' => $wonPredictions + $lostPredictions,
            'won' => $wonPredictions,
            'lost' => $lostPredictions,
            'pending' => $pendingPredictions,
            'accuracy' => $overallAccuracy
        ];

        // Daily Performance (Last 30 days)
        $dailyStats = $predictions
            ->filter(function ($item) {
                return Carbon::parse($item->match_date)->greaterThanOrEqualTo(now()->subDays(30));
            })
            ->groupBy(function ($item) {
                return Carbon::parse($item->match_date)->format('M d');
            })->map(function ($group) {
                $total = $group->count();
                $won = $group->where('status', 'won')->count();
                $lost = $group->where('status', 'lost')->count();
                $accuracy = $total > 0 ? round(($won / $total) * 100, 1) : 0;
                return compact('total', 'won', 'lost', 'accuracy');
            })
            ->sortKeys();

        // Monthly Performance
        $monthlyStats = $predictions->groupBy(function ($item) {
            return Carbon::parse($item->match_date)->format('M Y');
        })->map(function ($group) {
            $total = $group->count();
            $won = $group->where('status', 'won')->count();
            $lost = $group->where('status', 'lost')->count();
            $accuracy = $total > 0 ? round(($won / $total) * 100, 1) : 0;
            return compact('total', 'won', 'lost', 'accuracy');
        });

        return view('admin.performance', compact('dailyStats', 'monthlyStats', 'overallStats'));
    }
}
