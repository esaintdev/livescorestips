<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VipResult;
use App\Models\VipResultItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminVipResultController extends Controller
{
    public function index()
    {
        $results = VipResult::withCount('items')->orderBy('date', 'desc')->paginate(15);
        return view('admin.vip-results.index', compact('results'));
    }

    public function create()
    {
        return view('admin.vip-results.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'total_odds' => 'required|numeric',
            'win_rate_display' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.league_name' => 'required|string',
            'items.*.match_time' => 'required',
            'items.*.home_team' => 'required|string',
            'items.*.away_team' => 'required|string',
            'items.*.tip' => 'required|string',
            'items.*.odds' => 'nullable|numeric|min:0',
        ]);

        $vipResult = VipResult::create([
            'date' => $request->date,
            'total_odds' => $request->total_odds,
            'status' => 'pending',
            'win_rate_display' => $request->win_rate_display,
            'stage' => 'draft',
        ]);

        foreach ($request->items as $itemData) {
            $winner = $itemData['winner'] ?? '';
            $vipResult->items()->create([
                'league_name' => $itemData['league_name'],
                'match_time' => $itemData['match_time'],
                'home_team' => $itemData['home_team'],
                'away_team' => $itemData['away_team'],
                'home_score' => $itemData['home_score'] ?? null,
                'away_score' => $itemData['away_score'] ?? null,
                'tip' => $itemData['tip'],
                'odds' => $itemData['odds'] ?? null,
                'is_won' => $winner === '' ? null : ($winner === 'won' ? true : false),
            ]);
        }

        return redirect()->route('admin.vip-results.index')->with('success', 'VIP Result created as draft.');
    }

    public function edit(VipResult $vipResult)
    {
        $vipResult->load('items');
        return view('admin.vip-results.edit', compact('vipResult'));
    }

    public function update(Request $request, VipResult $vipResult)
    {
        $request->validate([
            'date' => 'required|date',
            'total_odds' => 'nullable|numeric',
            'status' => 'nullable|in:won,lost,pending',
            'win_rate_display' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.odds' => 'nullable|numeric|min:0',
        ]);

        $vipResult->update([
            'date' => $request->date,
            'total_odds' => $request->total_odds ?? $vipResult->total_odds,
            'status' => $request->status ?? $vipResult->status,
            'win_rate_display' => $request->win_rate_display,
        ]);

        $vipResult->items()->delete();

        $allResolved = true;
        $allWon = true;
        $productOdds = 1;
        $hasOdds = false;

        foreach ($request->items as $itemData) {
            $winner = $itemData['winner'] ?? '';

            $isWon = $winner === '' ? null : ($winner === 'won' ? true : false);

            if ($isWon === null) {
                $allResolved = false;
            } elseif (!$isWon) {
                $allWon = false;
            }

            $odds = isset($itemData['odds']) && $itemData['odds'] !== '' ? (float) $itemData['odds'] : null;
            if ($odds !== null) {
                $productOdds *= $odds;
                $hasOdds = true;
            }

            $vipResult->items()->create([
                'league_name' => $itemData['league_name'],
                'match_time' => $itemData['match_time'],
                'home_team' => $itemData['home_team'],
                'away_team' => $itemData['away_team'],
                'home_score' => $itemData['home_score'] ?? null,
                'away_score' => $itemData['away_score'] ?? null,
                'tip' => $itemData['tip'],
                'odds' => $itemData['odds'] ?? null,
                'is_won' => $isWon,
            ]);
        }

        // Auto-complete: transition from published to completed when all items resolved
        if ($allResolved && $vipResult->stage === 'published') {
            $vipResult->update([
                'stage' => 'completed',
                'status' => $allWon ? 'won' : 'lost',
                'total_odds' => $hasOdds ? round($productOdds, 2) : $vipResult->total_odds,
            ]);
        }

        return redirect()->route('admin.vip-results.index')->with('success', 'VIP Result updated successfully.');
    }

    public function publish(VipResult $vipResult)
    {
        $vipResult->update(['stage' => 'published']);
        return redirect()->route('admin.vip-results.index')->with('success', 'VIP Result published to subscribers.');
    }

    public function destroy(VipResult $vipResult)
    {
        $vipResult->delete();
        return redirect()->route('admin.vip-results.index')->with('success', 'VIP Result deleted successfully.');
    }
}
