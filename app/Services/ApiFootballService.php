<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ApiFootballService
{
    protected $apiKey;
    protected $apiHost;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.api_football.key');
        $this->apiHost = config('services.api_football.host');
        $this->baseUrl = 'https://' . $this->apiHost;
    }

    /**
     * Get the headers for the API request
     */
    protected function getHeaders()
    {
        $headers = [
            'x-apisports-key' => $this->apiKey,
        ];

        // Only add RapidAPI headers if the host is RapidAPI
        if (str_contains($this->apiHost, 'rapidapi.com')) {
            $headers['x-rapidapi-key'] = $this->apiKey;
            $headers['x-rapidapi-host'] = $this->apiHost;
        }

        return $headers;
    }

    /**
     * Fetch fixtures by date and optional league
     */
    public function fetchFixtures($date, $leagueId = null)
    {
        $cacheKey = "fixtures_{$date}" . ($leagueId ? "_{$leagueId}" : '');

        return Cache::remember($cacheKey, 3600, function () use ($date, $leagueId) {
            $isMonth = strlen($date) === 7; // format "YYYY-MM"
            $dateObj = \Carbon\Carbon::parse($isMonth ? $date . '-01' : $date);
            
            // Determine season based on the SEARCHED DATE (not today)
            // Club leagues run Aug–May, so before June we use the prior year as season.
            // International tournaments (World Cup, Euros) run in summer and use the current calendar year.
            $season = ($dateObj->month < 6) ? ($dateObj->year - 1) : $dateObj->year;

            $params = [
                'season' => $season,
            ];

            if ($isMonth) {
                if (!$leagueId) {
                    throw new \Exception("League is required when searching by month.");
                }
                $params['from'] = $dateObj->copy()->startOfMonth()->format('Y-m-d');
                $params['to'] = $dateObj->copy()->endOfMonth()->format('Y-m-d');
                $params['league'] = $leagueId;
            } else {
                $params['date'] = $date ?? date('Y-m-d');
                if ($leagueId) {
                    $params['league'] = $leagueId;
                }
            }

            // Use correct headers with increased timeout for large responses
            $response = Http::timeout(60)
                ->retry(2, 100)
                ->withHeaders($this->getHeaders())
                ->get($this->baseUrl . '/fixtures', $params);

            if ($response->successful()) {
                $data = $response->json()['response'] ?? [];

                // Don't cache empty results — always retry on next request
                if (empty($data)) {
                    Cache::forget("fixtures_{$date}" . ($leagueId ? "_{$leagueId}" : ''));
                }

                return $data;
            }

            return [];
        });
    }

    /**
     * Fetch fixtures for a date range and specific leagues
     */
    public function fetchFixturesByRange($startDate, $endDate, array $leagueIds = [])
    {
        $allFixtures = [];
        $currentDate = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        while ($currentDate->lte($end)) {
            $dateStr = $currentDate->format('Y-m-d');

            // If specific leagues are requested, we iterate them. 
            // Optimizing: The API supports one league per call or all leagues per date.
            // If we have a small list of leagues, it's better to filter AFTER fetching all for that date 
            // OR make separate calls if the list is very small. 
            // Given "Top Leagues" are about 8-10, making 10 calls per day might be heavy.
            // Better strategy: Fetch ALL fixtures for the date, then filter in PHP.

            $fixtures = $this->fetchFixtures($dateStr);

            foreach ($fixtures as $fixture) {
                // Filter by league if specified
                if (!empty($leagueIds) && !in_array($fixture['league']['id'], $leagueIds)) {
                    continue;
                }

                $allFixtures[] = $fixture;
            }

            $currentDate->addDay();
        }

        return $allFixtures;
    }

    /**
     * Fetch odds for a specific fixture
     */
    public function fetchOdds($fixtureId, $bookmaker = null)
    {
        $cacheKey = "odds_{$fixtureId}" . ($bookmaker ? "_{$bookmaker}" : '');

        return Cache::remember($cacheKey, 3600, function () use ($fixtureId, $bookmaker) {
            $params = ['fixture' => $fixtureId];
            if ($bookmaker) {
                $params['bookmaker'] = $bookmaker;
            }

            $response = Http::timeout(30)->retry(3, 100)->withHeaders($this->getHeaders())
                ->get($this->baseUrl . '/odds', $params);

            if ($response->successful()) {
                return $response->json()['response'] ?? [];
            }

            return [];
        });
    }

    /**
     * Fetch all odds for a specific date and bookmaker (Bulk)
     */
    public function fetchOddsByDate($date, $bookmakerId = 11)
    {
        $cacheKey = "bulk_odds_{$date}_{$bookmakerId}";

        return Cache::remember($cacheKey, 3600, function () use ($date, $bookmakerId) {
            $allOdds = [];
            $page = 1;
            $totalPages = 1;

            do {
                $response = Http::timeout(60)->withHeaders($this->getHeaders())
                    ->get($this->baseUrl . '/odds', [
                        'date' => $date,
                        'bookmaker' => $bookmakerId,
                        'page' => $page
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $allOdds = array_merge($allOdds, $data['response'] ?? []);
                    $totalPages = $data['paging']['total'] ?? 1;
                    $page++;
                } else {
                    break;
                }
            } while ($page <= $totalPages);

            return $allOdds;
        });
    }

    /**
     * Fetch odds for a single fixture (Fallback)
     */
    public function fetchOddsByFixture($fixtureId)
    {
        $cacheKey = "fixture_odds_{$fixtureId}";

        return Cache::remember($cacheKey, 3600, function () use ($fixtureId) {
            $response = Http::timeout(30)->retry(3, 100)->withHeaders($this->getHeaders())
                ->get($this->baseUrl . '/odds', [
                    'fixture' => $fixtureId
                ]);

            if ($response->successful()) {
                return $response->json()['response'] ?? [];
            }

            return [];
        });
    }

    /**
     * Fetch team's last N fixtures (for form)
     */
    public function fetchTeamForm($teamId, $last = 5)
    {
        $cacheKey = "team_form_{$teamId}_{$last}";

        return Cache::remember($cacheKey, 3600, function () use ($teamId, $last) {
            $currentYear = (int) date('Y');
            $currentMonth = (int) date('n');

            $seasons = [];
            $seasons[] = ($currentMonth < 6) ? ($currentYear - 1) : $currentYear;
            $seasons[] = ($currentMonth < 6) ? $currentYear : ($currentYear - 1);

            $seen = [];
            $fixtures = [];

            foreach (array_unique($seasons) as $season) {
                $response = Http::timeout(30)->retry(3, 100)->withHeaders($this->getHeaders())
                    ->get($this->baseUrl . '/fixtures', [
                        'team' => $teamId,
                        'last' => $last,
                        'season' => $season,
                    ]);

                if (!$response->successful()) {
                    continue;
                }

                $seasonFixtures = $response->json()['response'] ?? [];

                foreach ($seasonFixtures as $fixture) {
                    $id = $fixture['fixture']['id'] ?? null;
                    if ($id && !isset($seen[$id])) {
                        $seen[$id] = true;
                        $fixtures[] = $fixture;
                    }
                }
            }

            if (!empty($fixtures)) {
                usort($fixtures, function ($a, $b) {
                    return strtotime($b['fixture']['date'] ?? '0') <=> strtotime($a['fixture']['date'] ?? '0');
                });
                $fixtures = array_slice($fixtures, 0, $last);
            }

            return $this->parseTeamForm($fixtures, $teamId);
        });
    }

    public function fetchTeamGoalMetrics($teamId, $last = 5)
    {
        $cacheKey = "team_goal_metrics_{$teamId}_{$last}";

        return Cache::remember($cacheKey, 3600, function () use ($teamId, $last) {
            $currentYear = (int) date('Y');
            $currentMonth = (int) date('n');

            $seasons = [];
            $seasons[] = ($currentMonth < 6) ? ($currentYear - 1) : $currentYear;
            $seasons[] = ($currentMonth < 6) ? $currentYear : ($currentYear - 1);

            $seen = [];
            $fixtures = [];

            foreach (array_unique($seasons) as $season) {
                $response = Http::timeout(30)->retry(3, 100)->withHeaders($this->getHeaders())
                    ->get($this->baseUrl . '/fixtures', [
                        'team' => $teamId,
                        'last' => $last,
                        'season' => $season,
                    ]);

                if (!$response->successful()) continue;

                $seasonFixtures = $response->json()['response'] ?? [];
                foreach ($seasonFixtures as $fixture) {
                    $id = $fixture['fixture']['id'] ?? null;
                    if ($id && !isset($seen[$id])) {
                        $seen[$id] = true;
                        $fixtures[] = $fixture;
                    }
                }
            }

            if (!empty($fixtures)) {
                usort($fixtures, function ($a, $b) {
                    return strtotime($b['fixture']['date'] ?? '0') <=> strtotime($a['fixture']['date'] ?? '0');
                });
                $fixtures = array_slice($fixtures, 0, $last);
            }

            $totalGoals = 0;
            $totalGoalsFor = 0;
            $totalGoalsAgainst = 0;
            $bttsCount = 0;
            $count = count($fixtures);

            foreach ($fixtures as $fixture) {
                $homeId = $fixture['teams']['home']['id'];
                $homeGoals = (int)($fixture['goals']['home'] ?? 0);
                $awayGoals = (int)($fixture['goals']['away'] ?? 0);
                $totalGoals += $homeGoals + $awayGoals;

                if ($homeId == $teamId) {
                    $totalGoalsFor += $homeGoals;
                    $totalGoalsAgainst += $awayGoals;
                } else {
                    $totalGoalsFor += $awayGoals;
                    $totalGoalsAgainst += $homeGoals;
                }

                if ($homeGoals > 0 && $awayGoals > 0) {
                    $bttsCount++;
                }
            }

            return [
                'matches' => $count,
                'avg_total_goals' => $count > 0 ? round($totalGoals / $count, 2) : null,
                'avg_goals_for' => $count > 0 ? round($totalGoalsFor / $count, 2) : null,
                'avg_goals_against' => $count > 0 ? round($totalGoalsAgainst / $count, 2) : null,
                'btts_rate' => $count > 0 ? round($bttsCount / $count, 2) : null,
            ];
        });
    }

    /**
     * Parse team form from fixtures
     */
    protected function parseTeamForm($fixtures, $teamId)
    {
        $form = [];

        foreach ($fixtures as $fixture) {
            $homeId = $fixture['teams']['home']['id'];
            $awayId = $fixture['teams']['away']['id'];
            $homeGoals = $fixture['goals']['home'];
            $awayGoals = $fixture['goals']['away'];

            if ($homeId == $teamId) {
                // Team played at home
                if ($homeGoals > $awayGoals) {
                    $form[] = 'W';
                } elseif ($homeGoals < $awayGoals) {
                    $form[] = 'L';
                } else {
                    $form[] = 'D';
                }
            } else {
                // Team played away
                if ($awayGoals > $homeGoals) {
                    $form[] = 'W';
                } elseif ($awayGoals < $homeGoals) {
                    $form[] = 'L';
                } else {
                    $form[] = 'D';
                }
            }
        }

        return array_reverse($form); // Most recent first
    }

    /**
     * Calculate probability from decimal odds
     */
    public static function calculateProbability($odds)
    {
        if ($odds <= 0) {
            return 0;
        }

        return round((1 / $odds) * 100, 2);
    }

    /**
     * Fetch complete fixture details
     */
    public function fetchFixtureDetails($fixtureId)
    {
        $cacheKey = "fixture_details_{$fixtureId}";

        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $response = Http::timeout(30)->retry(3, 100)->withHeaders($this->getHeaders())
            ->get($this->baseUrl . '/fixtures', [
                'id' => $fixtureId,
            ]);

        if ($response->successful()) {
            $data = $response->json()['response'] ?? [];
            $fixture = $data[0] ?? null;

            if ($fixture) {
                $status = $fixture['fixture']['status']['short'] ?? 'NS';
                $isLive = in_array($status, ['1H', 'HT', '2H', 'ET', 'BT', 'P', 'SUSP', 'INT', 'LIVE']);

                // 60 seconds if live, 3600 seconds (1 hour) otherwise to save API quota
                $ttl = $isLive ? 60 : 3600;

                \Illuminate\Support\Facades\Cache::put($cacheKey, $fixture, $ttl);
            }
            return $fixture;
        }

        return null;
    }

    /**
     * Fetch head-to-head matches between two teams
     */
    public function fetchHeadToHead($team1Id, $team2Id, $last = 5)
    {
        $cacheKey = "h2h_{$team1Id}_{$team2Id}_{$last}";

        return Cache::remember($cacheKey, 3600, function () use ($team1Id, $team2Id, $last) {
            $response = Http::timeout(30)->retry(3, 100)->withHeaders($this->getHeaders())
                ->get($this->baseUrl . '/fixtures/headtohead', [
                    'h2h' => "{$team1Id}-{$team2Id}",
                    'last' => $last,
                ]);

            if ($response->successful()) {
                return $response->json()['response'] ?? [];
            }

            return [];
        });
    }

    /**
     * Fetch league standings
     */
    public function fetchLeagueStandings($leagueId, $season, $returnAllGroups = false)
    {
        $cacheKey = "standings_v2_{$leagueId}_{$season}";

        return Cache::remember($cacheKey, 3600, function () use ($leagueId, $season, $returnAllGroups) {
            try {
                $response = Http::timeout(5)->withHeaders($this->getHeaders())
                    ->get($this->baseUrl . '/standings', [
                        'league' => $leagueId,
                        'season' => $season,
                    ]);

                if ($response->successful()) {
                    $data = $response->json()['response'] ?? [];
                    $standings = $data[0]['league']['standings'] ?? [];
                    return $returnAllGroups ? $standings : ($standings[0] ?? []);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('API Football Standings Timeout: ' . $e->getMessage());
            }

            return [];
        });
    }

    /**
     * Fetch team statistics for a specific league/season
     */
    public function fetchTeamStatistics($teamId, $leagueId, $season)
    {
        $cacheKey = "team_stats_{$teamId}_{$leagueId}_{$season}";

        return Cache::remember($cacheKey, 3600, function () use ($teamId, $leagueId, $season) {
            try {
                $response = Http::timeout(5)->withHeaders($this->getHeaders())
                    ->get($this->baseUrl . '/teams/statistics', [
                        'team' => $teamId,
                        'league' => $leagueId,
                        'season' => $season,
                    ]);

                if ($response->successful()) {
                    $data = $response->json()['response'] ?? null;
                    return $data;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('API Football Team Stats Timeout: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Fetch all currently live fixtures
     */
    public function fetchLiveFixtures()
    {
        $cacheKey = 'live_fixtures';

        return Cache::remember($cacheKey, 60, function () { // Cache for 60 seconds
            try {
                $response = Http::timeout(5)->withHeaders($this->getHeaders())
                    ->get($this->baseUrl . '/fixtures', ['live' => 'all']);

                if ($response->successful()) {
                    return $response->json()['response'] ?? [];
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('API Football Live Fixtures Timeout: ' . $e->getMessage());
            }

            return [];
        });
    }

    /**
     * Get the count of currently live fixtures
     */
    public function fetchLiveFixturesCount()
    {
        $liveFixtures = $this->fetchLiveFixtures();
        return count($liveFixtures);
    }

    /**
     * Fetch league results (past fixtures)
     */
    public function fetchLeagueResults($leagueId, $count = 10)
    {
        $cacheKey = "league_results_{$leagueId}_{$count}";

        return Cache::remember($cacheKey, 3600, function () use ($leagueId, $count) {
            try {
                $response = Http::timeout(5)->withHeaders($this->getHeaders())
                    ->get($this->baseUrl . '/fixtures', [
                        'league' => $leagueId,
                        'last' => $count,
                        'status' => 'FT-AET-PEN' // Finished matches
                    ]);

                if ($response->successful()) {
                    return $response->json()['response'] ?? [];
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('API Football League Results Timeout: ' . $e->getMessage());
            }

            return [];
        });
    }

    /**
     * Fetch upcoming fixtures for a league
     */
    public function fetchLeagueUpcomingFixtures($leagueId, $count = 10)
    {
        $cacheKey = "league_fixtures_upcoming_{$leagueId}_{$count}";

        return Cache::remember($cacheKey, 3600, function () use ($leagueId, $count) {
            try {
                $response = Http::timeout(5)->withHeaders($this->getHeaders())
                    ->get($this->baseUrl . '/fixtures', [
                        'league' => $leagueId,
                        'next' => $count
                    ]);

                if ($response->successful()) {
                    return $response->json()['response'] ?? [];
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('API Football League Upcoming Fixtures Timeout: ' . $e->getMessage());
            }

            return [];
        });
    }

    /**
     * Get league details by ID
     */
    public function getLeagueById($leagueId)
    {
        $popular = $this->getPopularLeagues();
        $league = collect($popular)->firstWhere('id', (int) $leagueId);

        if ($league) {
            return $league;
        }

        // Fallback: fetch from API if not in popular list
        $cacheKey = "league_info_{$leagueId}";
        return Cache::remember($cacheKey, 86400, function () use ($leagueId) {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->baseUrl . '/leagues', [
                    'id' => $leagueId
                ]);

            if ($response->successful()) {
                $data = $response->json()['response'] ?? [];
                if (!empty($data)) {
                    return [
                        'id' => $data[0]['league']['id'],
                        'name' => $data[0]['league']['name'],
                        'country' => $data[0]['country']['name'],
                        'logo' => $data[0]['league']['logo'] ?? null,
                        'flag' => $data[0]['country']['flag'] ?? null
                    ];
                }
            }

            return null;
        });
    }

    /**
     * Fetch all leagues
     */
    public function fetchAllLeagues()
    {
        $cacheKey = 'all_leagues_list';

        // Only use cache if it exists; otherwise fetch and manually put it in cache if valid
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (!empty($cached)) {
                return $cached;
            }
        }

        $response = Http::timeout(15)->retry(2, 100)->withHeaders($this->getHeaders())
            ->get($this->baseUrl . '/leagues');

        if ($response->successful()) {
            $data = $response->json()['response'] ?? [];
            if (!empty($data)) {
                // Only cache if we actually got data
                Cache::put($cacheKey, $data, 86400); // 24 hours
                return $data;
            }
        }

        return [];
    }

    /**
     * Fetch all countries
     */
    public function fetchCountries()
    {
        $cacheKey = 'countries_list';

        // Only use cache if it exists; otherwise fetch and manually put it in cache if valid
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (!empty($cached)) {
                return $cached;
            }
        }

        $response = Http::timeout(15)->retry(2, 100)->withHeaders($this->getHeaders())
            ->get($this->baseUrl . '/countries');

        if ($response->successful()) {
            $data = $response->json()['response'] ?? [];
            if (!empty($data)) {
                // Only cache if we actually got data
                Cache::put($cacheKey, $data, 86400); // 24 hours
                return $data;
            }
        }

        return [];
    }

    /**
     * Get popular leagues — merges the admin-configured priority leagues
     * from Settings so the admin fixture-search dropdown always reflects
     * whatever tournaments are live (World Cup, Euros, etc.).
     */
    public function getPopularLeagues()
    {
        // Hardcoded base list with full metadata (name, slug, logo, flag)
        $baseLeagues = [
            ['id' => 1,   'name' => 'World Cup',         'slug' => 'world-cup',         'country' => 'World',   'logo' => 'https://upload.wikimedia.org/wikipedia/en/thumb/1/17/2026_FIFA_World_Cup_emblem.svg/330px-2026_FIFA_World_Cup_emblem.svg.png'],
            ['id' => 4,   'name' => 'Euro Championship', 'slug' => 'euro-championship', 'country' => 'World',   'logo' => 'https://media.api-sports.io/football/leagues/4.png'],
            ['id' => 6,   'name' => 'Africa Cup of Nations', 'slug' => 'afcon',          'country' => 'World',   'logo' => 'https://media.api-sports.io/football/leagues/6.png'],
            ['id' => 9,   'name' => 'Copa America',      'slug' => 'copa-america',      'country' => 'World',   'logo' => 'https://media.api-sports.io/football/leagues/9.png'],
            ['id' => 39,  'name' => 'Premier League',    'slug' => 'premier-league',    'country' => 'England', 'logo' => 'https://media.api-sports.io/football/leagues/39.png',  'flag' => 'https://media.api-sports.io/flags/gb.svg'],
            ['id' => 140, 'name' => 'La Liga',           'slug' => 'la-liga',           'country' => 'Spain',   'logo' => 'https://media.api-sports.io/football/leagues/140.png', 'flag' => 'https://media.api-sports.io/flags/es.svg'],
            ['id' => 78,  'name' => 'Bundesliga',        'slug' => 'bundesliga',        'country' => 'Germany', 'logo' => 'https://media.api-sports.io/football/leagues/78.png',  'flag' => 'https://media.api-sports.io/flags/de.svg'],
            ['id' => 135, 'name' => 'Serie A',           'slug' => 'serie-a',           'country' => 'Italy',   'logo' => 'https://media.api-sports.io/football/leagues/135.png', 'flag' => 'https://media.api-sports.io/flags/it.svg'],
            ['id' => 61,  'name' => 'Ligue 1',           'slug' => 'ligue-1',           'country' => 'France',  'logo' => 'https://media.api-sports.io/football/leagues/61.png',  'flag' => 'https://media.api-sports.io/flags/fr.svg'],
            ['id' => 94,  'name' => 'Primeira Liga',     'slug' => 'primeira-liga',     'country' => 'Portugal','logo' => 'https://media.api-sports.io/football/leagues/94.png',  'flag' => 'https://media.api-sports.io/flags/pt.svg'],
            ['id' => 88,  'name' => 'Eredivisie',        'slug' => 'eredivisie',        'country' => 'Netherlands','logo' => 'https://media.api-sports.io/football/leagues/88.png','flag' => 'https://media.api-sports.io/flags/nl.svg'],
            ['id' => 2,   'name' => 'Champions League',  'slug' => 'champions-league',  'country' => 'World',   'logo' => 'https://media.api-sports.io/football/leagues/2.png'],
            ['id' => 3,   'name' => 'Europa League',     'slug' => 'europa-league',     'country' => 'World',   'logo' => 'https://media.api-sports.io/football/leagues/3.png'],
            ['id' => 848, 'name' => 'Conference League', 'slug' => 'conference-league', 'country' => 'World',   'logo' => 'https://media.api-sports.io/football/leagues/848.png'],
        ];

        // Build a map of IDs already in the base list for quick lookup
        $baseIds = array_column($baseLeagues, 'id');

        // Merge any extra IDs the admin added via Settings that are NOT already in the base list
        try {
            $prioritySetting = \App\Models\Setting::get('priority_leagues', '');
            if ($prioritySetting) {
                $adminIds = array_map('intval', array_filter(array_map('trim', explode(',', $prioritySetting))));
                foreach ($adminIds as $adminId) {
                    if (!in_array($adminId, $baseIds)) {
                        // Unknown league — add a placeholder; name will show on fixture search results
                        $baseLeagues[] = [
                            'id'      => $adminId,
                            'name'    => "League #$adminId",
                            'slug'    => "league-$adminId",
                            'country' => 'World',
                            'logo'    => "https://media.api-sports.io/football/leagues/{$adminId}.png",
                        ];
                        $baseIds[] = $adminId;
                    }
                }
            }
        } catch (\Exception $e) {
            // Fail silently — DB may not be available during install
        }

        return $baseLeagues;
    }
}
