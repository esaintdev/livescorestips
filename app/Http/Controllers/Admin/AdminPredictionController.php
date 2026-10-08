<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prediction;
use App\Services\ApiFootballService;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Subscriber;
use App\Notifications\NewPredictionNotification;
use App\Notifications\BatchedPredictionsNotification;
use Illuminate\Support\Facades\Notification;

class AdminPredictionController extends Controller
{
    protected $apiService;

    public function __construct(ApiFootballService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request)
    {
        $query = Prediction::query();

        // Filter by search (team names or league)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('home_team', 'like', "%{$search}%")
                    ->orWhere('away_team', 'like', "%{$search}%")
                    ->orWhere('league_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('match_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('match_date', '<=', $request->date_to);
        }

        // Filter by banker
        if ($request->filled('banker')) {
            $query->where('is_banker', $request->banker === 'yes');
        }

        // Filter by month
        if ($request->filled('month')) {
            $date = Carbon::parse($request->month . '-01');
            $query->whereYear('match_date', $date->year)
                ->whereMonth('match_date', $date->month);
        }

        // Get all predictions and apply custom sorting
        $allPredictions = $query->get();

        $sortedPredictions = $this->sortByLeaguePriority($allPredictions);

        // Paginate the sorted results
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $predictions = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedPredictions->forPage($currentPage, $perPage),
            $sortedPredictions->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Calculate stats from ALL predictions (not filtered)
        $stats = [
            'total' => Prediction::count(),
            'won' => Prediction::where('status', 'won')->count(),
            'lost' => Prediction::where('status', 'lost')->count(),
            'pending' => Prediction::where('status', 'pending')->count(),
        ];

        return view('admin.predictions.index', compact('predictions', 'stats'));
    }

    public function create(Request $request)
    {
        $fixtureData = null;

        // If fixture_id is provided, fetch fixture details from API
        if ($request->has('fixture_id')) {
            $fixtureId = $request->input('fixture_id');
            try {
                $fixtureData = $this->apiService->fetchFixtureDetails($fixtureId);
            } catch (\Exception $e) {
                \Log::error('Error fetching fixture details', [
                    'fixture_id' => $fixtureId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $leagues = $this->apiService->getPopularLeagues();
        return view('admin.predictions.create', compact('leagues', 'fixtureData'));
    }

    public function fetchFixtures(Request $request)
    {
        try {
            $date = $request->input('date', now()->format('Y-m-d'));
            $leagueId = $request->input('league_id');

            \Log::info('Fetching fixtures', ['date' => $date, 'league_id' => $leagueId]);

            $fixtures = $this->apiService->fetchFixtures($date, $leagueId);

            \Log::info('Fixtures fetched', ['count' => count($fixtures)]);

            return response()->json(['fixtures' => $fixtures]);
        } catch (\Exception $e) {
            \Log::error('Error fetching fixtures', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to fetch fixtures: ' . $e->getMessage(),
                'fixtures' => []
            ], 500);
        } finally {
            // Free up connection after API call
            \DB::disconnect();
        }
    }

    public function fetchOdds(Request $request)
    {
        try {
            $fixtureId = $request->input('fixture_id');

            \Log::info('Fetching odds', ['fixture_id' => $fixtureId]);

            $odds = $this->apiService->fetchOdds($fixtureId);

            \Log::info('Odds fetched', ['count' => count($odds)]);

            return response()->json(['odds' => $odds]);
        } catch (\Exception $e) {
            \Log::error('Error fetching odds', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch odds: ' . $e->getMessage(),
                'odds' => []
            ], 500);
        } finally {
            // Free up connection after API call
            \DB::disconnect();
        }
    }

    public function fetchTeamForm(Request $request)
    {
        try {
            $teamId = $request->input('team_id');

            \Log::info('Fetching team form', ['team_id' => $teamId]);

            $form = $this->apiService->fetchTeamForm($teamId, 5);

            \Log::info('Team form fetched', ['form' => $form]);

            return response()->json(['form' => $form]);
        } catch (\Exception $e) {
            \Log::error('Error fetching team form', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch team form: ' . $e->getMessage(),
                'form' => []
            ], 500);
        } finally {
            // Free up connection after API call
            \DB::disconnect();
        }
    }

    public function store(Request $request)
    {
        \Log::info('Creating prediction', $request->all());

        $request->validate([
            'fixture_id' => 'required|integer',
            'league_id' => 'required|integer',
            'league_name' => 'required|string',
            'home_team' => 'required|string',
            'home_team_logo' => 'nullable|string',
            'away_team' => 'required|string',
            'away_team_logo' => 'nullable|string',
            'match_date' => 'required|date',
            'match_time' => 'required|string',
            'tip' => 'required|string',
            'category' => 'nullable|string',
            'odds' => 'required|numeric|min:1',
            'bookmaker_name' => 'nullable|string',
            'bookmaker_url' => 'nullable|url',
            'bookmaker_image' => 'nullable|string',
            'status' => 'required|in:pending,won,lost',
            'admin_note' => 'nullable|string',
            'home_score' => 'nullable|integer|min:0',
            'away_score' => 'nullable|integer|min:0',
        ]);

        $imagePath = $request->bookmaker_image;
        if ($request->hasFile('bookmaker_image')) {
            $imagePath = $request->file('bookmaker_image')->store('bookmakers', 'public');
        }

        $probability = ApiFootballService::calculateProbability($request->odds);

        // Generate slug from team names
        $slug = Prediction::generateSlug($request->home_team, $request->away_team, $request->fixture_id);

        $prediction = Prediction::create([
            'fixture_id' => $request->fixture_id,
            'league_id' => $request->league_id,
            'slug' => $slug,
            'league_name' => $request->league_name,
            'league_country' => $request->league_country,
            'league_logo' => $request->league_logo,
            'home_team' => $request->home_team,
            'home_team_logo' => $request->home_team_logo,
            'away_team' => $request->away_team,
            'away_team_logo' => $request->away_team_logo,
            'home_score' => $request->home_score,
            'away_score' => $request->away_score,
            'match_date' => $request->match_date,
            'match_time' => $request->match_time,
            'home_form' => $request->home_form,
            'away_form' => $request->away_form,
            'tip' => $request->tip,
            'category' => $request->category,
            'odds' => $request->odds,
            'probability' => $probability,
            'bookmaker_name' => $request->bookmaker_name,
            'bookmaker_image' => $imagePath,
            'bookmaker_url' => $request->bookmaker_url,
            'status' => $request->status,
            'is_featured' => $request->has('is_featured'),
            'is_banker' => $request->has('is_banker'),
            'admin_note' => $request->admin_note,
        ]);

        // Send Telegram notification
        try {
            $telegramService = new TelegramService();
            $telegramService->sendPrediction($prediction);
        } catch (\Exception $e) {
            \Log::error('Failed to send Telegram notification: ' . $e->getMessage());
            // Don't fail the prediction creation if Telegram fails
        }

        // Note: Email notifications are now sent in batches via the admin panel
        // instead of immediately when each prediction is created

        return redirect()->route('admin.predictions.index')->with('success', 'Prediction created successfully');
    }

    public function edit(Prediction $prediction)
    {
        return view('admin.predictions.edit', compact('prediction'));
    }

    public function update(Request $request, Prediction $prediction)
    {
        $request->validate([
            'tip' => 'required|string',
            'category' => 'nullable|string',
            'odds' => 'required|numeric|min:1',
            'status' => 'required|in:pending,won,lost',
            'admin_note' => 'nullable|string',
            'home_score' => 'nullable|integer|min:0',
            'away_score' => 'nullable|integer|min:0',
        ]);

        $probability = ApiFootballService::calculateProbability($request->odds);

        $prediction->update([
            'tip' => $request->tip,
            'category' => $request->category,
            'odds' => $request->odds,
            'probability' => $probability,
            'bookmaker_name' => $request->bookmaker_name,
            'bookmaker_url' => $request->bookmaker_url,
            'status' => $request->status,
            'admin_note' => $request->admin_note,
            'home_score' => $request->home_score,
            'away_score' => $request->away_score,
            'is_featured' => $request->has('is_featured'),
            'is_banker' => $request->has('is_banker'),
        ]);

        return redirect()->route('admin.predictions.index')->with('success', 'Prediction updated successfully');
    }

    public function destroy(Prediction $prediction)
    {
        \Log::info('Destroy method called for prediction: ' . $prediction->id);
        $prediction->delete();

        return back()->with('success', 'Prediction deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:predictions,id',
        ]);

        $count = count($request->ids);
        Prediction::whereIn('id', $request->ids)->delete();

        return back()->with('success', "$count predictions deleted successfully.");
    }

    public function bulkBanker(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:predictions,id',
            'action' => 'required|in:mark,unmark',
        ]);

        $count = count($request->ids);
        $value = $request->action === 'mark';
        Prediction::whereIn('id', $request->ids)->update(['is_banker' => $value]);

        $action = $value ? 'marked as' : 'removed from';
        return back()->with('success', "$count predictions $action banker successfully.");
    }

    public function toggleBanker(Prediction $prediction)
    {
        $prediction->update(['is_banker' => !$prediction->is_banker]);

        return back()->with('success', $prediction->is_banker
            ? '"' . $prediction->home_team . ' vs ' . $prediction->away_team . '" marked as banker.'
            : 'Banker status removed from "' . $prediction->home_team . ' vs ' . $prediction->away_team . '".');
    }

    public function checkStatus(Prediction $prediction, \App\Services\PredictionService $predictionService)
    {
        $status = $predictionService->checkStatus($prediction);

        if ($status === 'pending') {
            return back()->with('info', 'Status could not be determined or match is not finished.');
        }

        return back()->with('success', "Prediction status updated to {$status}.");
    }

    public function showUpdateStatusPage()
    {
        return view('admin.predictions.update-status');
    }

    public function updateAllStatuses()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('prediction:update-status');
            $output = \Illuminate\Support\Facades\Artisan::output();
            return back()->with('success', 'Update command executed successfully.')->with('command_output', $output);
        } catch (\Exception $e) {
            return back()->with('error', 'Error executing command: ' . $e->getMessage());
        }
    }

    public function sendBatchedNotifications()
    {
        try {
            // Get only TODAY's predictions that haven't been sent yet
            $today = now()->format('Y-m-d');

            $unsentPredictions = Prediction::whereNull('notification_sent_at')
                ->whereDate('match_date', $today)
                ->orderBy('match_time', 'asc')
                ->get();

            if ($unsentPredictions->isEmpty()) {
                return back()->with('info', 'No unsent predictions for today to notify subscribers about.');
            }

            // Send batched notification to subscribers in chunks
            Subscriber::where('is_active', true)->chunk(100, function ($subscribers) use ($unsentPredictions) {
                if ($subscribers->count() > 0) {
                    Notification::send($subscribers, new BatchedPredictionsNotification($unsentPredictions));
                }
            });

            // Mark only today's predictions as sent
            Prediction::whereNull('notification_sent_at')
                ->whereDate('match_date', $today)
                ->update(['notification_sent_at' => now()]);

            $count = $unsentPredictions->count();
            \Log::info("Sent batched notification with {$count} predictions for {$today} to subscribers");

            return back()->with('success', "Successfully sent {$count} prediction" . ($count > 1 ? 's' : '') . " for today to all subscribers!");
        } catch (\Exception $e) {
            \Log::error("Batched Notification Error: " . $e->getMessage());
            return back()->with('error', 'Failed to send notifications: ' . $e->getMessage());
        } finally {
            // Free up connection after operation
            \DB::disconnect();
        }
    }

    public function bankers(Request $request)
    {
        $query = Prediction::where('is_banker', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('home_team', 'like', "%{$search}%")
                    ->orWhere('away_team', 'like', "%{$search}%")
                    ->orWhere('league_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('match_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('match_date', '<=', $request->date_to);
        }

        $allPredictions = $query->get();
        $sortedPredictions = $this->sortByLeaguePriority($allPredictions);

        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $predictions = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedPredictions->forPage($currentPage, $perPage),
            $sortedPredictions->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.predictions.bankers', compact('predictions'));
    }

    private function getUefaFifaCompetitions(): array
    {
        return [
            'UEFA Champions League', 'UEFA Europa League', 'UEFA Europa Conference League',
            'UEFA Nations League', 'FIFA World Cup', 'World Cup',
            'UEFA European Championship', 'Euro Championship',
            'Africa Cup of Nations', 'Copa America', 'UEFA Super Cup', 'FIFA Club World Cup',
        ];
    }

    private function getTopEuropeanLeagues(): array
    {
        return [
            'Premier League', 'La Liga', 'Serie A', 'Bundesliga',
            'Ligue 1', 'Primeira Liga', 'Eredivisie',
        ];
    }

    private function getLeaguePriority($leagueName, array $uefaFifa, array $topEuropean, array $popularNames): int
    {
        foreach ($uefaFifa as $comp) {
            if (stripos($leagueName, $comp) !== false) return 1;
        }
        foreach ($topEuropean as $league) {
            if (stripos($leagueName, $league) !== false) return 2;
        }
        foreach ($popularNames as $name) {
            if (stripos($leagueName, $name) !== false) return 2;
        }
        return 3;
    }

    private function sortByLeaguePriority($predictions)
    {
        $popularLeagues = $this->apiService->getPopularLeagues();
        $popularNames = array_column($popularLeagues, 'name');
        $uefaFifa = $this->getUefaFifaCompetitions();
        $topEuropean = $this->getTopEuropeanLeagues();

        return $predictions->sort(function ($a, $b) use ($uefaFifa, $topEuropean, $popularNames) {
            $priorityA = $this->getLeaguePriority($a->league_name, $uefaFifa, $topEuropean, $popularNames);
            $priorityB = $this->getLeaguePriority($b->league_name, $uefaFifa, $topEuropean, $popularNames);

            if ($a->match_date !== $b->match_date) {
                return strtotime($b->match_date) - strtotime($a->match_date);
            }
            if ($priorityA !== $priorityB) {
                return $priorityA - $priorityB;
            }
            return strcmp($a->match_time, $b->match_time);
        })->values();
    }
}

