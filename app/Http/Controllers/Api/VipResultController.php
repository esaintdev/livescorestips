<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Metadata;
use App\Models\VipResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VipResultController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);

        $results = VipResult::with('items')
            ->where('stage', 'completed')
            ->orderBy('date', 'desc')
            ->paginate($perPage);

        $wonCount = VipResult::where('stage', 'completed')->where('status', 'won')->count();
        $totalCompleted = VipResult::where('stage', 'completed')->whereIn('status', ['won', 'lost'])->count();
        $overallWinRate = $totalCompleted > 0
            ? round(($wonCount / $totalCompleted) * 100, 1)
            : 98.5;

        $metadata = Metadata::where('route_name', 'vip-results.index')->pluck('value', 'key');

        return response()->json([
            'data' => $results->through(fn ($r) => $this->toResult($r)),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'total' => $results->total(),
                'overall_win_rate' => $overallWinRate,
                'page_title' => $metadata['page_title'] ?? null,
                'page_subheader' => $metadata['page_subheader'] ?? null,
                'page_description' => $metadata['page_description'] ?? null,
            ],
        ]);
    }

    private function toResult(VipResult $result): array
    {
        return [
            'id' => $result->id,
            'date' => $result->date ? $result->date->format('Y-m-d') : null,
            'total_odds' => $result->total_odds,
            'status' => $result->status,
            'win_rate_display' => $result->win_rate_display,
            'items' => $result->items->map(fn ($item) => [
                'league_name' => $item->league_name,
                'match_time' => $item->match_time,
                'home_team' => $item->home_team,
                'away_team' => $item->away_team,
                'home_score' => $item->home_score,
                'away_score' => $item->away_score,
                'tip' => $item->tip,
                'odds' => $item->odds,
                'is_won' => $item->is_won,
            ])->values(),
        ];
    }
}
