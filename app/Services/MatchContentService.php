<?php

namespace App\Services;

use App\Models\Prediction;
use Carbon\Carbon;

/**
 * Builds match-specific editorial content for the match detail page:
 * prediction analysis, key match factors and related predictions.
 *
 * Every statement is derived from data that is actually available. Nothing
 * is fabricated: when a data source is missing the corresponding sentence or
 * factor is simply omitted.
 */
class MatchContentService
{
    /** Category name => public category slug. */
    public const CATEGORY_SLUGS = [
        'Double Chance' => 'double-chance',
        'Over 1.5 Goals' => 'over-1-5-goals',
        'Over 2.5 Goals' => 'over-2-5-goals',
        'Under 2.5 Goals' => 'over-2-5-goals',
        'Both Teams To Score' => 'btts-gg',
        'Draw' => 'draws',
        'Home Win' => 'home-wins',
        'Away Win' => 'away-wins',
        'Correct Score' => 'correct-score',
    ];

    /**
     * Public category slug for a prediction's market, if one exists.
     */
    public function categorySlug(Prediction $prediction): ?string
    {
        return self::CATEGORY_SLUGS[$prediction->category] ?? null;
    }

    /**
     * Unique, market-aware analysis built from available match data.
     *
     * @param  array  $ctx  home_form, away_form, h2h, standings, home_stats, away_stats,
     *                      home_team, away_team, home_team_id, away_team_id
     */
    public function analysis(Prediction $prediction, array $ctx = []): string
    {
        $home = $ctx['home_team'] ?? $prediction->home_team;
        $away = $ctx['away_team'] ?? $prediction->away_team;
        $tip = trim((string) $prediction->tip);
        $market = $prediction->category ?: 'this market';

        $homeForm = $this->normalizeForm($ctx['home_form'] ?? []);
        $awayForm = $this->normalizeForm($ctx['away_form'] ?? []);
        $h2h = $ctx['h2h'] ?? [];
        $standings = $ctx['standings'] ?? [];
        $homeTeamId = $ctx['home_team_id'] ?? null;
        $awayTeamId = $ctx['away_team_id'] ?? null;

        $sentences = [];

        // Form context (only when both sides have form data).
        if (! empty($homeForm) && ! empty($awayForm)) {
            $h = $this->formRecord($homeForm);
            $a = $this->formRecord($awayForm);
            $sentences[] = sprintf(
                '%s come into this fixture with a recent record of %d win%s, %d draw%s and %d defeat%s, while %s have %d win%s, %d draw%s and %d defeat%s in the same window.',
                $home,
                $h['w'],
                $h['w'] === 1 ? '' : 's',
                $h['d'],
                $h['d'] === 1 ? '' : 's',
                $h['l'],
                $h['l'] === 1 ? '' : 's',
                $away,
                $a['w'],
                $a['w'] === 1 ? '' : 's',
                $a['d'],
                $a['d'] === 1 ? '' : 's',
                $a['l'],
                $a['l'] === 1 ? '' : 's'
            );
        }

        // League position context.
        $homeRow = $this->findStandingRow($standings, $homeTeamId);
        $awayRow = $this->findStandingRow($standings, $awayTeamId);
        if ($homeRow && $awayRow) {
            $sentences[] = sprintf(
                '%s currently sit %s on %s points, with %s placed %s on %s points.',
                $home,
                $this->ordinal($homeRow['rank'] ?? null),
                $homeRow['points'] ?? 0,
                $away,
                $this->ordinal($awayRow['rank'] ?? null),
                $awayRow['points'] ?? 0
            );
        }

        // H2H context.
        $h2hStats = $this->h2hStats($h2h);
        if ($h2hStats['count'] > 0) {
            $sentences[] = sprintf(
                'Across the last %d head-to-head meetings, %d goal%s have been scored and both teams found the net in %d of them.',
                $h2hStats['count'],
                $h2hStats['goals'],
                $h2hStats['goals'] === 1 ? '' : 's',
                $h2hStats['both_scored']
            );
        }

        // Market-specific reasoning.
        $reason = $this->marketReason($prediction, $tip, $market, $ctx, $h2hStats);
        if ($reason !== null) {
            $sentences[] = $reason;
        }

        // Always-true statistical anchor so the paragraph is never empty.
        if ($prediction->probability !== null) {
            $odds = $prediction->odds !== null ? ' at odds of '.rtrim(rtrim((string) $prediction->odds, '0'), '.') : '';
            $sentences[] = sprintf(
                'The %s selection (%s) is assigned a %s%% probability%s.',
                $market,
                $tip,
                rtrim(rtrim((string) $prediction->probability, '0'), '.'),
                $odds
            );
        }

        return trim(implode(' ', $sentences));
    }

    /**
     * 4-6 factual, data-backed key factors. Returns fewer when less data is
     * available rather than inventing content.
     */
    public function factors(Prediction $prediction, array $ctx = []): array
    {
        $factors = [];
        $home = $ctx['home_team'] ?? $prediction->home_team;
        $away = $ctx['away_team'] ?? $prediction->away_team;

        $homeForm = $this->normalizeForm($ctx['home_form'] ?? []);
        $awayForm = $this->normalizeForm($ctx['away_form'] ?? []);

        // 1. Recent form comparison.
        if (! empty($homeForm) && ! empty($awayForm)) {
            $h = $this->formRecord($homeForm);
            $a = $this->formRecord($awayForm);
            $hPts = $h['w'] * 3 + $h['d'];
            $aPts = $a['w'] * 3 + $a['d'];
            $leader = $hPts === $aPts ? null : ($hPts > $aPts ? $home : $away);
            $factors[] = $leader
                ? sprintf('%s have the better recent form (%d pts vs %d pts from the last %d matches).', $leader, max($hPts, $aPts), min($hPts, $aPts), max(count($homeForm), count($awayForm)))
                : sprintf('Both sides are level on recent form (%d pts each from their last matches).', $hPts);
        }

        // 2. Goals scored / conceded (season stats).
        $homeStats = $ctx['home_stats'] ?? null;
        $awayStats = $ctx['away_stats'] ?? null;
        if (is_array($homeStats) && is_array($awayStats)) {
            $hFor = $homeStats['goals']['for']['total']['total'] ?? null;
            $aFor = $awayStats['goals']['for']['total']['total'] ?? null;
            $hAgainst = $homeStats['goals']['against']['total']['total'] ?? null;
            $aAgainst = $awayStats['goals']['against']['total']['total'] ?? null;
            if ($hFor !== null && $aFor !== null) {
                $factors[] = sprintf('%s have scored %s goals this season compared with %s for %s.', $home, $hFor, $aFor, $away);
            }
            if ($hAgainst !== null && $aAgainst !== null) {
                $factors[] = sprintf('Defensively, %s have conceded %s and %s have conceded %s.', $home, $hAgainst, $away, $aAgainst);
            }
        }

        // 3. League position.
        $standings = $ctx['standings'] ?? [];
        $homeRow = $this->findStandingRow($standings, $ctx['home_team_id'] ?? null);
        $awayRow = $this->findStandingRow($standings, $ctx['away_team_id'] ?? null);
        if ($homeRow && $awayRow) {
            $factors[] = sprintf('League positions: %s %s, %s %s.', $home, $this->ordinal($homeRow['rank'] ?? null), $away, $this->ordinal($awayRow['rank'] ?? null));
        }

        // 4. Home/away performance split from standings.
        if ($homeRow && isset($homeRow['home']['win'], $homeRow['home']['played'])) {
            $factors[] = sprintf('%s have won %s of %s home matches in this competition.', $home, $homeRow['home']['win'], $homeRow['home']['played']);
        }
        if ($awayRow && isset($awayRow['away']['win'], $awayRow['away']['played'])) {
            $factors[] = sprintf('%s have won %s of %s away matches in this competition.', $away, $awayRow['away']['win'], $awayRow['away']['played']);
        }

        // 5. H2H pattern.
        $h2hStats = $this->h2hStats($ctx['h2h'] ?? []);
        if ($h2hStats['count'] > 0) {
            $avg = round($h2hStats['goals'] / $h2hStats['count'], 1);
            $factors[] = sprintf('The last %d H2H meetings average %s goals per game.', $h2hStats['count'], $avg);
        }

        // 6. Market-specific reason.
        $reason = $this->marketFactor($prediction);
        if ($reason !== null) {
            $factors[] = $reason;
        }

        return array_slice(array_values(array_unique($factors)), 0, 6);
    }

    /**
     * Related predictions: same market or league, excluding this record and
     * any other record for the same provider fixture.
     */
    public function related(Prediction $prediction, int $limit = 4)
    {
        $candidates = Prediction::query()
            ->whereNotNull('slug')
            ->where('id', '!=', $prediction->id)
            ->where('fixture_id', '!=', $prediction->fixture_id)
            ->where(function ($q) use ($prediction) {
                $q->where('category', $prediction->category);
                if ($prediction->league_id) {
                    $q->orWhere('league_id', $prediction->league_id);
                }
            })
            ->orderByDesc('match_date')
            ->limit(60)
            ->get();

        $referenceDate = $prediction->match_date ?: Carbon::now();

        $sorted = $candidates
            ->sortByDesc(function (Prediction $c) use ($prediction, $referenceDate) {
                $score = 0;
                if ($c->category === $prediction->category) {
                    $score += 4;
                }
                if ($prediction->league_id && $c->league_id === $prediction->league_id) {
                    $score += 2;
                }
                if ($c->match_date) {
                    $days = abs($c->match_date->diffInDays($referenceDate));
                    $score -= min($days, 30) / 30;
                }

                return $score;
            })
            ->values();

        // Link each provider fixture through its canonical record so related
        // links never propagate duplicate match URLs.
        $result = collect();
        $seenFixtures = [];

        foreach ($sorted as $candidate) {
            $canonical = Prediction::canonicalForFixture($candidate->fixture_id) ?? $candidate;

            if ($canonical->id === $prediction->id || $canonical->fixture_id === $prediction->fixture_id) {
                continue;
            }
            if (isset($seenFixtures[$canonical->fixture_id])) {
                continue;
            }

            $seenFixtures[$canonical->fixture_id] = true;
            $result->push($canonical);

            if ($result->count() >= $limit) {
                break;
            }
        }

        return $result;
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function normalizeForm($form): array
    {
        if (! is_array($form)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($r) {
            $r = strtoupper(trim((string) $r));

            return in_array($r, ['W', 'D', 'L'], true) ? $r : null;
        }, $form)));
    }

    private function formRecord(array $form): array
    {
        return [
            'w' => count(array_filter($form, fn ($r) => $r === 'W')),
            'd' => count(array_filter($form, fn ($r) => $r === 'D')),
            'l' => count(array_filter($form, fn ($r) => $r === 'L')),
        ];
    }

    private function findStandingRow(array $standings, $teamId): ?array
    {
        if (empty($standings) || $teamId === null) {
            return null;
        }

        foreach ($standings as $row) {
            if (($row['team']['id'] ?? null) == $teamId) {
                return $row;
            }
        }

        return null;
    }

    private function h2hStats(array $h2h): array
    {
        $count = 0;
        $goals = 0;
        $bothScored = 0;

        foreach ($h2h as $match) {
            $hg = $match['goals']['home'] ?? null;
            $ag = $match['goals']['away'] ?? null;
            if ($hg === null || $ag === null) {
                continue;
            }
            $count++;
            $goals += (int) $hg + (int) $ag;
            if ((int) $hg > 0 && (int) $ag > 0) {
                $bothScored++;
            }
        }

        return ['count' => $count, 'goals' => $goals, 'both_scored' => $bothScored];
    }

    private function ordinal($n): string
    {
        if ($n === null || ! is_numeric($n)) {
            return '—';
        }
        $n = (int) $n;
        $suffix = 'th';
        if (! in_array($n % 100, [11, 12, 13], true)) {
            $suffix = match ($n % 10) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
        }

        return $n.$suffix;
    }

    private function classify(string $tip): array
    {
        $t = strtoupper(trim($tip));

        if (preg_match('/(OVER|UNDER)\s+(\d+(\.\d+)?)/i', $t, $m)) {
            return ['type' => strtolower($m[1]) === 'over' ? 'over' : 'under', 'line' => $m[2]];
        }
        if ($t === 'BTTS' || $t === 'GG') {
            return ['type' => 'btts'];
        }
        if ($t === 'NG' || $t === 'NO GG') {
            return ['type' => 'ng'];
        }
        if (str_starts_with($t, '1X')) {
            return ['type' => 'dc_home'];
        }
        if (str_starts_with($t, 'X2')) {
            return ['type' => 'dc_away'];
        }
        if (str_starts_with($t, '12')) {
            return ['type' => 'dc_nodraw'];
        }
        if (in_array($t, ['1', 'HOME', 'HOME WIN'], true)) {
            return ['type' => 'home'];
        }
        if (in_array($t, ['2', 'AWAY', 'AWAY WIN'], true)) {
            return ['type' => 'away'];
        }
        if (in_array($t, ['X', 'DRAW'], true)) {
            return ['type' => 'draw'];
        }
        if (preg_match('/^(\d+)-(\d+)$/', $t)) {
            return ['type' => 'cs'];
        }

        return ['type' => 'unknown'];
    }

    private function marketReason(Prediction $prediction, string $tip, string $market, array $ctx, array $h2hStats): ?string
    {
        $home = $ctx['home_team'] ?? $prediction->home_team;
        $away = $ctx['away_team'] ?? $prediction->away_team;
        $homeForm = $this->normalizeForm($ctx['home_form'] ?? []);
        $awayForm = $this->normalizeForm($ctx['away_form'] ?? []);
        $type = $this->classify($tip);

        switch ($type['type']) {
            case 'over':
                if ($h2hStats['count'] > 0) {
                    $avg = round($h2hStats['goals'] / $h2hStats['count'], 1);

                    return "Recent head-to-head meetings average {$avg} goals, which supports the Over {$type['line']} line.";
                }

                return "The model favours goals in this fixture, supporting the Over {$type['line']} selection.";

            case 'under':
                if ($h2hStats['count'] > 0) {
                    $avg = round($h2hStats['goals'] / $h2hStats['count'], 1);

                    return "Recent head-to-head meetings average {$avg} goals, which supports the Under {$type['line']} line.";
                }

                return "The model expects a tight fixture, supporting the Under {$type['line']} selection.";

            case 'btts':
                if ($h2hStats['count'] > 0) {
                    return "Both teams scored in {$h2hStats['both_scored']} of the last {$h2hStats['count']} head-to-head meetings, backing the BTTS selection.";
                }

                return 'The model expects both attacks to register, backing the BTTS selection.';

            case 'ng':
                return 'The model expects at least one side to be kept off the scoresheet, backing the NG selection.';

            case 'home':
                return "The home side is favoured for this fixture, backing the {$home} win.";

            case 'away':
                return "The away side is favoured for this fixture, backing the {$away} win.";

            case 'draw':
                return 'The model rates the two sides evenly matched, backing the draw.';

            case 'dc_home':
                return "Combining the home win and the draw, the 1X selection covers the two most likely outcomes for {$home}.";

            case 'dc_away':
                return "Combining the away win and the draw, the X2 selection covers the two most likely outcomes for {$away}.";

            case 'dc_nodraw':
                return 'With both sides capable of winning, the 12 selection covers either team taking the points.';

            case 'cs':
                return "The predicted scoreline of {$tip} reflects the model's expected balance between the two attacks.";

            default:
                return null;
        }
    }

    private function marketFactor(Prediction $prediction): ?string
    {
        $type = $this->classify((string) $prediction->tip);

        return match ($type['type']) {
            'over' => "Market rationale: goals trend supports Over {$type['line']}.",
            'under' => "Market rationale: the model leans towards a low-scoring game for Under {$type['line']}.",
            'btts' => 'Market rationale: both teams carry a scoring threat.',
            'ng' => 'Market rationale: at least one defence is expected to hold firm.',
            'home' => 'Market rationale: home advantage and the model favour the home side.',
            'away' => 'Market rationale: the model favours the away side.',
            'draw' => 'Market rationale: the model sees the teams as evenly matched.',
            'dc_home' => 'Market rationale: 1X covers the home win and the draw.',
            'dc_away' => 'Market rationale: X2 covers the away win and the draw.',
            'dc_nodraw' => 'Market rationale: 12 covers either side winning.',
            'cs' => "Market rationale: correct score selection {$prediction->tip}.",
            default => null,
        };
    }
}
