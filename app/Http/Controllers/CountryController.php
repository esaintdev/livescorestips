<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prediction;

class CountryController extends Controller
{
    public function show(Request $request, string $name)
    {
        // Unknown countries are real 404s, not empty indexable pages
        if (! Prediction::where('league_country', $name)->exists()) {
            abort(404);
        }

        $requestedDate = $request->input('date');
        $hasDateFilter = is_string($requestedDate)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate)
            && checkdate((int) substr($requestedDate, 5, 2), (int) substr($requestedDate, 8, 2), (int) substr($requestedDate, 0, 4));
        $date = $hasDateFilter ? $requestedDate : now()->format('Y-m-d');

        // Without an explicit date, fall forward to the next day that has
        // tips so the canonical country page is never empty
        if (! $hasDateFilter && ! Prediction::where('league_country', $name)->whereDate('match_date', $date)->exists()) {
            $next = Prediction::where('league_country', $name)
                ->whereDate('match_date', '>', $date)
                ->whereDate('match_date', '<=', now()->addDays(7)->format('Y-m-d'))
                ->min('match_date');
            if ($next) {
                $date = \Carbon\Carbon::parse($next)->format('Y-m-d');
            }
        }

        // Generate date range (yesterday to +7 days)
        $dateRange = [];
        $start = now()->subDay();
        for ($i = 0; $i < 9; $i++) {
            $current = $start->copy()->addDays($i);
            $dateRange[] = [
                'date'     => $current->format('Y-m-d'),
                'day'      => $current->format('d'),
                'is_today' => $current->isToday(),
                'label'    => $current->isToday() ? 'TODAY' : $current->format('D'),
            ];
        }

        // Fetch upcoming predictions for this country on the selected date
        $predictions = Prediction::where('league_country', $name)
            ->whereDate('match_date', $date)
            ->orderBy('match_date', 'asc')
            ->orderBy('match_time', 'asc')
            ->get();

        $formattedDate = \Carbon\Carbon::parse($date)->format('M j, Y');
        $canonical = route('country.show', ['name' => $name]);

        return view('country.show', compact(
            'predictions',
            'name',
            'date',
            'dateRange',
            'formattedDate',
            'hasDateFilter',
            'canonical'
        ));
    }
}
