<?php

namespace App\Http\Controllers;

use App\Models\VipResult;
use Illuminate\Http\Request;

class VipResultController extends Controller
{
    /**
     * Display a listing of the VIP results.
     */
    public function index()
    {
        $results = VipResult::with('items')
            ->where('stage', 'completed')
            ->orderBy('date', 'desc')
            ->paginate(10);

        $completedCount = VipResult::where('stage', 'completed');
        $wonCount = (clone $completedCount)->where('status', 'won')->count();
        $totalCompleted = (clone $completedCount)->whereIn('status', ['won', 'lost'])->count();

        $overallWinRate = $totalCompleted > 0
            ? round(($wonCount / $totalCompleted) * 100, 1)
            : 98.5;

        $metadata = \App\Models\Metadata::where('route_name', 'vip-results.index')->pluck('value', 'key');

        return view('vip-results.index', compact('results', 'overallWinRate', 'metadata'));
    }
}
