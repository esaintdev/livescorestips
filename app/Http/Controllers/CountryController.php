<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prediction;

class CountryController extends Controller
{
    public function show(Request $request, string $name)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

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

        return view('country.show', compact(
            'predictions',
            'name',
            'date',
            'dateRange',
            'formattedDate'
        ));
    }
}
