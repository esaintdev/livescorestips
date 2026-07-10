<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class PredictionService
{
    protected $apiService;

    public function __construct(ApiFootballService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Automatically generate predictions for a specific date.
     * Processes ALL leagues equally — no tier-based gating.
     * Every eligible fixture with available odds gets a chance.
     *
     * @param string $date Y-m-d
     * @param int $limit Number of predictions to generate
     * @param array $leagueIds Optional array of league IDs to filter by
     * @return array The generated predictions
     */
    public function generateAutoPredictions($date, $limit = 5, $leagueIds = [])
    {
        $leagueSuffix = !empty($leagueIds) ? ' (league_ids: ' . implode(',', $leagueIds) . ')' : '';
        Log::info("Starting auto-prediction generation for $date with limit $limit" . $leagueSuffix);

        // 1. Fetch Bulk Odds for the whole day (1xBet)
        Log::info("Fetching bulk odds for $date (1xBet)...");
        $bulkOdds = $this->apiService->fetchOddsByDate($date, 11);

        // Map bulk odds by fixture ID for O(1) lookup
        $oddsMap = [];
        foreach ($bulkOdds as $entry) {
            $fId = $entry['fixture']['id'] ?? null;
            if ($fId) {
                $oddsMap[$fId] = $entry;
            }
        }
        Log::info("Mapped " . count($oddsMap) . " fixtures with available 1xBet odds.");

        // 2. Fetch ALL eligible fixtures for the date (optionally filtered by league)
        $allFixturesRaw = $this->fetchEligibleFixtures($date, $leagueIds);
        Log::info("Found " . count($allFixturesRaw) . " eligible fixtures for $date" . $leagueSuffix);

        // Shuffle all fixtures to give every league an equal chance
        shuffle($allFixturesRaw);

        $generatedPredictions = [];
        $categoryCounts = [];
        $skippedReasons = ['duplicate' => 0, 'no_analysis' => 0, 'no_odds' => 0];

        // Track how many deep analyses / individual odds calls we've done to avoid excessive API calls
        $deepAnalysisCount = 0;
        $maxDeepAnalysis = 30;
        $individualOddsCalls = 0;
        $maxIndividualOddsCalls = 50;

        // 3. Process ALL fixtures equally — no tier gating
        foreach ($allFixturesRaw as $fixture) {
            if (count($generatedPredictions) >= $limit)
                break;

            $useDeepAnalysis = $deepAnalysisCount < $maxDeepAnalysis;
            $canCallIndividualOdds = $individualOddsCalls < $maxIndividualOddsCalls;
            $this->processFixtureWithFallback(
                $fixture,
                $generatedPredictions,
                $categoryCounts,
                $skippedReasons,
                $oddsMap,
                $useDeepAnalysis,
                $canCallIndividualOdds,
                $individualOddsCalls
            );

            if ($useDeepAnalysis) {
                $deepAnalysisCount++;
            }
        }

        Log::info("Prediction generation summary: Generated=" . count($generatedPredictions) . ", Total Fixtures Processed=" . count($allFixturesRaw) . ", Deep Analyses=" . $deepAnalysisCount . ", Individual Odds Calls=" . $individualOddsCalls);

        $totalChecked = count($generatedPredictions) + $skippedReasons['duplicate'] + $skippedReasons['no_analysis'] + $skippedReasons['no_odds'];

        return [
            'predictions' => $generatedPredictions,
            'summary' => array_merge($skippedReasons, ['total_checked' => $totalChecked])
        ];
    }

    /**
     * Internal processor to handle a single fixture.
     * All fixtures are treated equally — no tier gating.
     * Uses deep analysis (form + H2H) for the first N fixtures, quick odds analysis for the rest.
     * Individual odds fallback for EVERY fixture (capped to protect API quota).
     */
    protected function processFixtureWithFallback($fixture, &$generatedPredictions, &$categoryCounts, &$skippedReasons, $oddsMap, $useDeepAnalysis = false, $canCallIndividualOdds = false, &$individualOddsCalls = 0)
    {
        $fixtureId = $fixture['fixture']['id'];
        $leagueName = $fixture['league']['name'];
        $matchName = "{$fixture['teams']['home']['name']} vs {$fixture['teams']['away']['name']}";

        // 1. DUPLICATE CHECK — skip only if there's already a REAL prediction (with a tip)
        $existing = Prediction::where('fixture_id', $fixtureId)->first();
        if ($existing && $existing->tip !== null && $existing->tip !== '' && $existing->tip !== 'Awaiting odds') {
            $skippedReasons['duplicate']++;
            return;
        }
        
        // If there's a placeholder record, delete it so we can replace with a real prediction
        if ($existing) {
            $existing->delete();
        }

        // 2. STATUS CHECK
        $status = $fixture['fixture']['status']['short'];
        if (!in_array($status, ['NS', 'TBD'])) {
            $skippedReasons['no_analysis']++;
            return;
        }

        // 3. ODDS LOOKUP — bulk first, individual fallback for EVERY fixture (capped at maxIndividualOddsCalls)
        $bulkEntry = $oddsMap[$fixtureId] ?? null;
        if (!$bulkEntry && $canCallIndividualOdds) {
            $individualOddsCalls++;
            $singleOdds = $this->apiService->fetchOddsByFixture($fixtureId);
            if (!empty($singleOdds)) {
                $bulkEntry = $singleOdds[0];
            }
        }

        if (!$bulkEntry) {
            $skippedReasons['no_odds']++;
            return;
        }

        // 4. ANALYZE: Deep analysis for some, quick odds analysis for the rest
        if ($useDeepAnalysis) {
            $potentialPredictions = $this->analyzeMatch($fixture);
            $logSuffix = " (Deep Analysis)";
        } else {
            $potentialPredictions = $this->quickAnalyzeMatch($bulkEntry);
            $logSuffix = " (Quick Odds Analysis)";
        }

        // FALLBACK: If analysis returned nothing, try safe double-chance / goals market
        if (empty($potentialPredictions)) {
            $fallbackTips = ['1X', 'X2', '12', 'Over 1.5', 'Over 0.5'];
            foreach ($fallbackTips as $fTip) {
                $mType = ($fTip === 'Over 1.5' || $fTip === 'Over 0.5') ? 'goals' : 'double_chance';
                $fOdds = $this->extractOddsFromEntry($bulkEntry, $mType, $fTip);

                if ($fOdds && $fOdds['value'] >= 1.05) {
                    $potentialPredictions[] = [
                        'tip' => $fTip,
                        'category' => ($mType === 'goals') ? ($fTip === 'Over 1.5' ? 'Over 1.5 Goals' : 'Over 0.5 Goals') : 'Double Chance',
                        'market_type' => $mType,
                        'home_form' => [],
                        'away_form' => []
                    ];
                    break;
                }
            }
        }

        if (!empty($potentialPredictions)) {
            // Ensure Diversity: Pick a category that hasn't been picked yet today if possible
            $allPossibleCategories = [
                'Home Win',
                'Away Win',
                'Both Teams To Score',
                'Correct Score',
                'Double Chance',
                'Over 1.5 Goals',
                'Over 2.5 Goals',
                'Under 2.5 Goals'
            ];

            $missingCategories = array_filter($allPossibleCategories, function ($cat) use ($categoryCounts) {
                return ($categoryCounts[$cat] ?? 0) === 0;
            });

            $diversityCandidate = null;
            if (!empty($missingCategories)) {
                $shuffled = $potentialPredictions;
                shuffle($shuffled);
                foreach ($shuffled as $pp) {
                    if (in_array($pp['category'], $missingCategories)) {
                        $diversityCandidate = $pp;
                        break;
                    }
                }
            }

            if ($diversityCandidate) {
                $predictionData = $diversityCandidate;
            } else {
                // Otherwise pick least used (randomized among ties)
                usort($potentialPredictions, function ($a, $b) use ($categoryCounts) {
                    $countA = $categoryCounts[$a['category']] ?? 0;
                    $countB = $categoryCounts[$b['category']] ?? 0;
                    return $countA <=> $countB;
                });

                $minCount = $categoryCounts[$potentialPredictions[0]['category']] ?? 0;
                $bestCandidates = array_filter($potentialPredictions, function ($p) use ($categoryCounts, $minCount) {
                    return ($categoryCounts[$p['category']] ?? 0) === $minCount;
                });
                $predictionData = $bestCandidates[array_rand($bestCandidates)];
            }

            // Extract odds from the pre-found bulk entry
            $odds = $this->extractOddsFromEntry($bulkEntry, $predictionData['market_type'], $predictionData['tip']);

            if ($odds) {
                $prediction = $this->createPredictionRecord(
                    $fixture,
                    $predictionData,
                    $odds,
                    $predictionData['home_form'] ?? [],
                    $predictionData['away_form'] ?? []
                );
                $generatedPredictions[] = $prediction;

                $cat = $predictionData['category'];
                $categoryCounts[$cat] = ($categoryCounts[$cat] ?? 0) + 1;

                Log::info("Successfully created prediction for $matchName ($leagueName)" . $logSuffix);
            } else {
                Log::warning("Skipped $matchName: Specific market/tip odds not found in 1xBet data.");
                $skippedReasons['no_odds']++;
            }
        } else {
            Log::warning("Skipped $matchName: No strong analysis signal.");
            $skippedReasons['no_analysis']++;
        }
    }

    /**
     * Fast analysis based purely on odds. Zero extra API calls.
     * Relaxed odds filter to include ALL leagues (was previously 1.20-3.20).
     */
    protected function quickAnalyzeMatch($bulkEntry)
    {
        $potentialPredictions = [];
        if (empty($bulkEntry) || empty($bulkEntry['bookmakers']))
            return [];

        $bookmaker = $bulkEntry['bookmakers'][0];
        foreach ($bookmaker['bets'] as $bet) {
            $marketType = null;
            $marketName = $bet['name'];

            if (in_array($marketName, ['Match Winner', '1X2', 'Full Time Result'])) {
                $marketType = '1x2';
            } elseif (in_array($marketName, ['Goals Over/Under', 'Total Goals', 'Over/Under'])) {
                $marketType = 'goals';
            } elseif ($marketName === 'Double Chance') {
                $marketType = 'double_chance';
            } elseif (in_array($marketName, ['Both Teams To Score', 'BTTS'])) {
                $marketType = 'btts';
            }

            if ($marketType) {
                // Find outcome with lowest odds (highest probability)
                $values = $bet['values'];
                usort($values, fn($a, $b) => $a['odd'] <=> $b['odd']);
                $best = $values[0];

                // RELAXED QUALITY FILTER: 1.10 - 5.00 covers all leagues.
                // Smaller leagues may have higher odds, and that's fine —
                // the tip is still valid even if the return is lower confidence.
                if ($best['odd'] < 1.10 || $best['odd'] > 5.00)
                    continue;

                $tip = $this->normalizeTip($best['value'], $marketType);
                $category = null;

                if ($marketType === '1x2') {
                    if ($tip === 'X')
                        continue; // Skip Draws
                    $category = ($tip === '1') ? 'Home Win' : 'Away Win';
                } elseif ($marketType === 'goals') {
                    $val = floatval($this->extractGoalValue($best['value']));
                    if ($val == 0)
                        continue;
                    $type = (stripos($best['value'], 'Over') !== false) ? 'Over' : 'Under';
                    $category = "$type $val Goals";
                } elseif ($marketType === 'double_chance') {
                    $category = 'Double Chance';
                } elseif ($marketType === 'btts') {
                    $category = 'Both Teams To Score';
                }

                if ($category) {
                    $potentialPredictions[] = [
                        'tip' => $tip,
                        'category' => $category,
                        'market_type' => $marketType,
                        'home_form' => [],
                        'away_form' => []
                    ];
                }
            }
        }
        return $potentialPredictions;
    }

    protected function extractGoalValue($string)
    {
        if (preg_match('/(\d+(\.\d+)?)/', $string, $matches)) {
            return $matches[1];
        }
        return 0;
    }

    protected function fetchEligibleFixtures($date, $leagueIds = [])
    {
        // Use the existing range method but for same start/end date
        $fixtures = $this->apiService->fetchFixturesByRange($date, $date, $leagueIds);

        return array_filter($fixtures, function ($fixture) {
            $statusShort = $fixture['fixture']['status']['short'];

            // 1. Must be Not Started or TBD
            if (!in_array($statusShort, ['NS', 'TBD'])) {
                return false;
            }

            // 2. Exclude women's, youth, and reserve games
            if ($this->isFixtureExcluded($fixture)) {
                return false;
            }

            return true;
        });
    }

    protected function isFixtureExcluded($fixture): bool
    {
        $leagueName = $fixture['league']['name'] ?? '';
        $homeTeam = $fixture['teams']['home']['name'] ?? '';
        $awayTeam = $fixture['teams']['away']['name'] ?? '';

        // Check league name patterns
        if (preg_match('/\bU(19|20|21|22|23)\b/i', $leagueName)) return true;
        if (preg_match('/\bReserve\b/i', $leagueName)) return true;
        if (preg_match('/\bWomen\b/i', $leagueName)) return true;
        if (preg_match('/\bWomens\b/i', $leagueName)) return true;
        if (preg_match('/\bFeminin\b/i', $leagueName)) return true;
        if (preg_match('/\bFemminile\b/i', $leagueName)) return true;
        if (preg_match('/\bFemenino\b/i', $leagueName)) return true;
        if (preg_match('/\bLadies\b/i', $leagueName)) return true;
        if (preg_match('/\bJunior\b/i', $leagueName)) return true;
        if (preg_match('/\bYouth\b/i', $leagueName)) return true;

        // Check team names
        foreach ([$homeTeam, $awayTeam] as $team) {
            if (preg_match('/\bU(19|20|21|22|23)\b/i', $team)) return true;
            if (preg_match('/\bWomen\b/i', $team)) return true;
            if (preg_match('/\bWFC\b/i', $team)) return true;
            if (preg_match('/\bLadies\b/i', $team)) return true;
            if (preg_match('/\bRes(\.|erve)\b/i', $team)) return true;
            if (preg_match('/\sII$/', $team)) return true;
            if (preg_match('/[- ]B$/', $team)) return true;
        }

        return false;
    }

    /**
     * Analyze a single match and return ALL potential tips
     * Relaxed logic to ensure coverage of all categories.
     */
    public function analyzeMatch($fixture)
    {
        $homeTeamId = $fixture['teams']['home']['id'];
        $awayTeamId = $fixture['teams']['away']['id'];

        // Fetch Form
        $homeForm = $this->apiService->fetchTeamForm($homeTeamId, 5);
        $awayForm = $this->apiService->fetchTeamForm($awayTeamId, 5);

        // Fetch goal metrics from recent matches (for Over/Under/BTTS conditioning)
        $homeGoalMetrics = $this->apiService->fetchTeamGoalMetrics($homeTeamId, 5);
        $awayGoalMetrics = $this->apiService->fetchTeamGoalMetrics($awayTeamId, 5);

        // Combined average total goals across both teams' recent matches
        $avgTotalGoals = null;
        if ($homeGoalMetrics['avg_total_goals'] !== null && $awayGoalMetrics['avg_total_goals'] !== null) {
            $avgTotalGoals = ($homeGoalMetrics['avg_total_goals'] + $awayGoalMetrics['avg_total_goals']) / 2;
        } elseif ($homeGoalMetrics['avg_total_goals'] !== null) {
            $avgTotalGoals = $homeGoalMetrics['avg_total_goals'];
        } elseif ($awayGoalMetrics['avg_total_goals'] !== null) {
            $avgTotalGoals = $awayGoalMetrics['avg_total_goals'];
        }

        // Combined BTTS rate
        $combinedBttsRate = null;
        if ($homeGoalMetrics['btts_rate'] !== null && $awayGoalMetrics['btts_rate'] !== null) {
            $combinedBttsRate = ($homeGoalMetrics['btts_rate'] + $awayGoalMetrics['btts_rate']) / 2;
        } elseif ($homeGoalMetrics['btts_rate'] !== null) {
            $combinedBttsRate = $homeGoalMetrics['btts_rate'];
        } elseif ($awayGoalMetrics['btts_rate'] !== null) {
            $combinedBttsRate = $awayGoalMetrics['btts_rate'];
        }

        // Fetch H2H (still useful for determining DIRECTION of the tip, even if we force the category)
        $h2h = $this->apiService->fetchHeadToHead($homeTeamId, $awayTeamId, 5);

        $homePoints = $this->calculateFormPoints($homeForm);
        $awayPoints = $this->calculateFormPoints($awayForm);

        // Calculate a simple strength metric to decide WHICH side of the bet to pick
        // e.g. for Home Wins, we pick '1' if Home is better, else we might skip or still pick it if forced?
        // "do what i want" -> "predict all category". 
        // So for "Home Wins" category, if Home is NOT favored, we probably shouldn't predict it. 
        // But we need to output SOMETHING for that category if possible? 
        // Actually, "Home Wins" implies the tip is "1". If Home is likely to lose, suggesting "1" is bad.
        // But "Double Chance" can cover "X2" (Away/Draw).
        // let's try to generate a valid tip for EVERY category if plausible.

        $homeStrength = $homePoints + 5; // Base 5
        $awayStrength = $awayPoints + 5;

        // Adjust strength based on H2H + calculate goal metrics
        $h2hTotalGoals = 0;
        $h2hBothScored = 0;
        $h2hCount = 0;
        foreach ($h2h as $match) {
            $h2hCount++;
            $hg = (int)($match['goals']['home'] ?? 0);
            $ag = (int)($match['goals']['away'] ?? 0);
            $h2hTotalGoals += $hg + $ag;
            if ($hg > 0 && $ag > 0) $h2hBothScored++;

            $winner = ($hg > $ag) ? 'home' : (($ag > $hg) ? 'away' : 'draw');
            if ($match['teams']['home']['id'] == $homeTeamId) {
                if ($winner == 'home')
                    $homeStrength += 1;
                elseif ($winner == 'away')
                    $awayStrength += 1;
            } else {
                if ($winner == 'away')
                    $homeStrength += 1;
                elseif ($winner == 'home')
                    $awayStrength += 1;
            }
        }

        $potentialPredictions = [];

        // 1. Home Win (1)
        // Relaxed: Just needs to be slightly better or equal
        if ($homeStrength >= $awayStrength) {
            $potentialPredictions[] = [
                'tip' => '1',
                'category' => 'Home Win',
                'market_type' => '1x2',
                'home_form' => $homeForm,
                'away_form' => $awayForm
            ];

        }

        // 2. Away Win (2) 
        if ($awayStrength >= $homeStrength) {
            $potentialPredictions[] = [
                'tip' => '2',
                'category' => 'Away Win',
                'market_type' => '1x2',
                'home_form' => $homeForm,
                'away_form' => $awayForm
            ];
        }

        // 4. Double Chance (1X, X2, 12)
        // Pick the most likely Double Chance
        if ($homeStrength > $awayStrength) {
            $tip = '1X';
        } elseif ($awayStrength > $homeStrength) {
            $tip = 'X2';
        } else {
            // Even
            $tip = '1X'; // Default to home advantage
        }
        $potentialPredictions[] = [
            'tip' => $tip,
            'category' => 'Double Chance',
            'market_type' => 'double_chance',
            'home_form' => $homeForm,
            'away_form' => $awayForm
        ];

        // 5. Over 1.5 Goals — skip only if both teams avg <1 goal per game
        if ($avgTotalGoals === null || $avgTotalGoals >= 1.0) {
            $potentialPredictions[] = [
                'tip' => 'Over 1.5',
                'category' => 'Over 1.5 Goals',
                'market_type' => 'goals',
                'home_form' => $homeForm,
                'away_form' => $awayForm
            ];
        }

        // 6/7. Over 2.5 or Under 2.5 — mutually exclusive, driven by form goal data
        if ($avgTotalGoals !== null) {
            if ($avgTotalGoals >= 2.5) {
                $potentialPredictions[] = [
                    'tip' => 'Over 2.5',
                    'category' => 'Over 2.5 Goals',
                    'market_type' => 'goals',
                    'home_form' => $homeForm,
                    'away_form' => $awayForm
                ];
            } else {
                $potentialPredictions[] = [
                    'tip' => 'Under 2.5',
                    'category' => 'Under 2.5 Goals',
                    'market_type' => 'goals',
                    'home_form' => $homeForm,
                    'away_form' => $awayForm
                ];
            }
        } else {
            // No form data: fall back to H2H
            $h2hAvg = $h2hCount > 0 ? $h2hTotalGoals / $h2hCount : null;
            if ($h2hAvg !== null && $h2hAvg >= 2.5) {
                $potentialPredictions[] = [
                    'tip' => 'Over 2.5',
                    'category' => 'Over 2.5 Goals',
                    'market_type' => 'goals',
                    'home_form' => $homeForm,
                    'away_form' => $awayForm
                ];
            } elseif ($h2hAvg !== null) {
                $potentialPredictions[] = [
                    'tip' => 'Under 2.5',
                    'category' => 'Under 2.5 Goals',
                    'market_type' => 'goals',
                    'home_form' => $homeForm,
                    'away_form' => $awayForm
                ];
            }
        }

        // 8. BTTS (Both Teams To Score) — driven by how often both teams score in their recent matches
        $offerBTTS = false;
        if ($combinedBttsRate !== null && $combinedBttsRate >= 0.3) {
            $offerBTTS = true;
        } elseif ($combinedBttsRate === null && $h2hCount > 0 && $h2hBothScored / $h2hCount >= 0.4) {
            $offerBTTS = true;
        }
        if ($offerBTTS) {
            $potentialPredictions[] = [
                'tip' => 'BTTS',
                'category' => 'Both Teams To Score',
                'market_type' => 'btts',
                'home_form' => $homeForm,
                'away_form' => $awayForm
            ];
        }



        // 9. Correct Score
        // We pick a plausible score based on strength
        $scoreTip = '1-0';
        if ($homeStrength > $awayStrength + 5)
            $scoreTip = '2-0';
        elseif ($homeStrength > $awayStrength + 2)
            $scoreTip = '2-1';
        elseif ($awayStrength > $homeStrength + 5)
            $scoreTip = '0-2';
        elseif ($awayStrength > $homeStrength + 2)
            $scoreTip = '1-2';
        elseif (abs($homeStrength - $awayStrength) <= 2)
            $scoreTip = '1-1';

        $potentialPredictions[] = [
            'tip' => $scoreTip,
            'category' => 'Correct Score',
            'market_type' => 'correct_score',
            'home_form' => $homeForm,
            'away_form' => $awayForm
        ];

        return $potentialPredictions;
    }

    protected function calculateFormPoints($form)
    {
        $points = 0;
        foreach ($form as $result) {
            if ($result === 'W')
                $points += 3;
            if ($result === 'D')
                $points += 1;
        }
        return $points;
    }

    public function extractOddsPublic($entry, $marketType, $tip)
    {
        return $this->extractOddsFromEntry($entry, $marketType, $tip);
    }

    protected function extractOddsFromEntry($entry, $marketType, $tip)
    {
        if (empty($entry) || empty($entry['bookmakers'])) {
            return null;
        }

        $bookmaker = $entry['bookmakers'][0];
        foreach ($bookmaker['bets'] as $bet) {
            $isTargetMarket = match ($marketType) {
                '1x2' => in_array($bet['name'], ['Match Winner', '1X2', 'Home/Away', 'Full Time Result']),
                'goals' => in_array($bet['name'], ['Goals Over/Under', 'Total Goals', 'Over/Under', 'Total']),
                'double_chance' => $bet['name'] === 'Double Chance',
                'btts' => in_array($bet['name'], ['Both Teams To Score', 'BTTS', 'Both Teams to Score', 'Both Teams Score']),
                'correct_score' => in_array($bet['name'], ['Correct Score', 'Exact Score']),
                default => false
            };

            if ($isTargetMarket) {
                foreach ($bet['values'] as $value) {
                    if ($this->normalizeTip($value['value'], $marketType) === $this->normalizeTip($tip, $marketType)) {
                        $odd = floatval($value['odd']);
                        // Final Guard: Never return trash odds
                        if ($odd < 1.10)
                            return null;

                        return [
                            'value' => $odd,
                            'bookmaker' => $bookmaker['name']
                        ];
                    }
                }
            }
        }

        return null;
    }

    protected function normalizeTip($raw, $marketType)
    {
        $raw = strtoupper(trim($raw));

        if ($marketType === '1x2') {
            if ($raw === 'HOME')
                return '1';
            if ($raw === 'AWAY')
                return '2';
            if ($raw === 'DRAW')
                return 'X';
        }

        if ($marketType === 'goals') {
            if (preg_match('/(OVER|UNDER)\s+(\d+(\.\d+)?)/i', $raw, $matches)) {
                return ucfirst(strtolower($matches[1])) . ' ' . $matches[2];
            }
        }

        if ($marketType === 'btts') {
            // Normalize BTTS tips
            if ($raw === 'YES' || $raw === 'GG' || stripos($raw, 'BOTH') !== false)
                return 'BTTS';
            if ($raw === 'NO' || $raw === 'NG')
                return 'NO';
        }

        if ($marketType === 'double_chance') {
            // Normalize double chance tips
            if (stripos($raw, 'HOME') !== false && stripos($raw, 'DRAW') !== false)
                return '1X';
            if (stripos($raw, 'AWAY') !== false && stripos($raw, 'DRAW') !== false)
                return 'X2';
            if (stripos($raw, 'HOME') !== false && stripos($raw, 'AWAY') !== false)
                return '12';
        }

        if ($marketType === 'correct_score') {
            // Normalize 2:1 to 2-1 or 2 - 1 to 2-1
            return str_replace([':', ' ', '–'], '-', $raw);
        }

        return $raw;
    }

    public function createPredictionRecord($fixture, $predictionData, $oddsData, $homeForm = [], $awayForm = [])
    {
        $homeTeam = $fixture['teams']['home']['name'];
        $awayTeam = $fixture['teams']['away']['name'];

        $slug = Prediction::generateSlug($homeTeam, $awayTeam, $fixture['fixture']['id']);

        $probability = ApiFootballService::calculateProbability($oddsData['value']);

        return Prediction::create([
            'fixture_id' => $fixture['fixture']['id'],
            'league_id' => $fixture['league']['id'],
            'slug' => $slug,
            'league_name' => $fixture['league']['name'],
            'league_country' => $fixture['league']['country'],
            'league_logo' => $fixture['league']['logo'],
            'home_team' => $homeTeam,
            'home_team_logo' => $fixture['teams']['home']['logo'],
            'away_team' => $awayTeam,
            'away_team_logo' => $fixture['teams']['away']['logo'],
            'match_date' => date('Y-m-d', strtotime($fixture['fixture']['date'])),
            'match_time' => date('H:i', strtotime($fixture['fixture']['date'])),
            'home_form' => $homeForm,
            'away_form' => $awayForm,
            'tip' => $predictionData['tip'],
            'category' => $predictionData['category'],
            'odds' => $oddsData['value'],
            'probability' => $probability,
            'bookmaker_name' => $oddsData['bookmaker'],
            'bookmaker_image' => null,
            'bookmaker_url' => null,
            'status' => 'pending',
            'is_featured' => false,
            'admin_note' => 'Auto-generated',
        ]);
    }

    /**
     * Check and update the status of a prediction based on the match outcome.
     *
     * @param Prediction $prediction
     * @return string The new status ('won', 'lost', 'pending')
     */
    public function checkStatus(Prediction $prediction)
    {
        $fixture = $this->apiService->fetchFixtureDetails($prediction->fixture_id);

        if (!$fixture) {
            Log::warning("Fixture not found for prediction ID: {$prediction->id}");
            return 'pending';
        }

        $statusShort = $fixture['fixture']['status']['short'];

        // Check if match is finished
        if (!in_array($statusShort, ['FT', 'AET', 'PEN'])) {
            return 'pending';
        }

        $homeGoals = $fixture['goals']['home'];
        $awayGoals = $fixture['goals']['away'];
        $tip = $prediction->tip;

        $outcome = $this->determineOutcome($tip, $homeGoals, $awayGoals);

        if ($outcome) {
            $prediction->update([
                'status' => $outcome,
                'home_score' => $homeGoals,
                'away_score' => $awayGoals
            ]);
            return $outcome;
        }

        return 'pending';
    }

    /**
     * Determine if the tip won or lost based on scores.
     *
     * @param string $tip
     * @param int $homeGoals
     * @param int $awayGoals
     * @return string|null 'won', 'lost', or null if undetermined
     */
    protected function determineOutcome($tip, $homeGoals, $awayGoals)
    {
        $tip = strtoupper(trim($tip));

        // 1x2 Betting
        if ($tip === '1' || $tip === 'HOME' || $tip === 'HOME WIN') {
            return $homeGoals > $awayGoals ? 'won' : 'lost';
        }
        if ($tip === '2' || $tip === 'AWAY' || $tip === 'AWAY WIN') {
            return $awayGoals > $homeGoals ? 'won' : 'lost';
        }
        if ($tip === 'X' || $tip === 'DRAW') {
            return $homeGoals === $awayGoals ? 'won' : 'lost';
        }

        // Double Chance
        // Check starts_with to handle formats like "1X (Home or Draw)"
        if (str_starts_with($tip, '1X')) {
            return $homeGoals >= $awayGoals ? 'won' : 'lost';
        }
        if (str_starts_with($tip, 'X2')) {
            return $awayGoals >= $homeGoals ? 'won' : 'lost';
        }
        if (str_starts_with($tip, '12')) {
            return $homeGoals !== $awayGoals ? 'won' : 'lost';
        }

        // Over/Under Goals (e.g., "Over 2.5", "Under 3.5", "Over 2.5 Goals")
        if (preg_match('/(OVER|UNDER)\s+(\d+(\.\d+)?)/i', $tip, $matches)) {
            $type = strtoupper($matches[1]);
            $threshold = floatval($matches[2]);
            $totalGoals = $homeGoals + $awayGoals;

            if ($type === 'OVER') {
                return $totalGoals > $threshold ? 'won' : 'lost';
            }
            if ($type === 'UNDER') {
                return $totalGoals < $threshold ? 'won' : 'lost';
            }
        }

        // BTTS (Both Teams To Score)
        // Matches: "BTTS", "GG", "BTTS/GG (Both Teams To Score)"
        if ($tip === 'BTTS' || $tip === 'GG' || str_contains($tip, 'BTTS') || str_contains($tip, 'BOTH TEAMS')) {
            return ($homeGoals > 0 && $awayGoals > 0) ? 'won' : 'lost';
        }
        // NG (No Goal)
        // Matches: "NG", "NO GG", "NG (No Goal)"
        if ($tip === 'NG' || $tip === 'NO GG' || str_contains($tip, 'NO GOAL')) {
            return ($homeGoals === 0 || $awayGoals === 0) ? 'won' : 'lost';
        }

        // Correct Score (e.g., "2-1")
        if (preg_match('/^(\d+)-(\d+)$/', $tip, $matches)) {
            $predictedHome = intval($matches[1]);
            $predictedAway = intval($matches[2]);
            return ($homeGoals === $predictedHome && $awayGoals === $predictedAway) ? 'won' : 'lost';
        }

        Log::warning("Unknown tip format: {$tip}");
        return null; // Could not determine
    }
}
