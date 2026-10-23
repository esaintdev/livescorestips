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
     * "Match preview" paragraphs: when/where, table situation, season scoring
     * and defensive record, recent scoring trend. Each paragraph only appears
     * when its data is available.
     *
     * @param  array  $ctx  as for analysis(), plus home_recent, away_recent
     * @return string[]
     */
    public function preview(Prediction $prediction, array $fixture, array $ctx = []): array
    {
        $home = $ctx['home_team'] ?? $prediction->home_team;
        $away = $ctx['away_team'] ?? $prediction->away_team;
        $paragraphs = [];

        // 1. When and where.
        $league = $fixture['league']['name'] ?? $prediction->league_name;
        $round = $fixture['league']['round'] ?? null;
        $kickoff = $this->kickoff($fixture, $prediction);
        $venue = $fixture['fixture']['venue']['name'] ?? null;
        $city = $fixture['fixture']['venue']['city'] ?? null;

        $when = sprintf('%s face %s', $home, $away);
        if ($league) {
            $when .= ' in the '.$league;
            if ($round && ! preg_match('/^regular season/i', $round)) {
                $when .= ' ('.$round.')';
            }
        }
        if ($kickoff) {
            $when .= ' on '.$kickoff->format('l j F Y').', kick-off '.$this->clock($kickoff);
        }
        if ($venue) {
            $when .= ' at '.$venue.($city ? ', '.$city : '');
        }
        $paragraphs[] = $when.'.';

        // 2. Table situation.
        $standings = $ctx['standings'] ?? [];
        $homeRow = $this->findStandingRow($standings, $ctx['home_team_id'] ?? null);
        $awayRow = $this->findStandingRow($standings, $ctx['away_team_id'] ?? null);
        if ($homeRow && $awayRow && isset($homeRow['points'], $awayRow['points'])) {
            $gap = (int) $homeRow['points'] - (int) $awayRow['points'];
            $played = $homeRow['all']['played'] ?? null;
            $text = sprintf(
                '%s are %s with %d points%s, while %s are %s with %d.',
                $home,
                $this->ordinal($homeRow['rank'] ?? null),
                $homeRow['points'],
                $played ? ' from '.$played.' games' : '',
                $away,
                $this->ordinal($awayRow['rank'] ?? null),
                $awayRow['points']
            );
            $text .= $gap === 0
                ? ' The two sides are level on points.'
                : sprintf(' That leaves %s %d point%s ahead.', $gap > 0 ? $home : $away, abs($gap), abs($gap) === 1 ? '' : 's');
            $paragraphs[] = $text;
        }

        // 3. Season scoring at home / on the road.
        $hs = $ctx['home_stats'] ?? null;
        $as = $ctx['away_stats'] ?? null;
        $hScored = is_array($hs) ? ($hs['goals']['for']['average']['home'] ?? null) : null;
        $hConceded = is_array($hs) ? ($hs['goals']['against']['average']['home'] ?? null) : null;
        $aScored = is_array($as) ? ($as['goals']['for']['average']['away'] ?? null) : null;
        $aConceded = is_array($as) ? ($as['goals']['against']['average']['away'] ?? null) : null;
        if ($hScored !== null && $hConceded !== null && $aScored !== null && $aConceded !== null) {
            $text = sprintf(
                'At home this season %s average %s goals scored and %s conceded per game; on the road %s average %s scored and %s conceded.',
                $home, $hScored, $hConceded, $away, $aScored, $aConceded
            );
            $hClean = $hs['clean_sheet']['total'] ?? null;
            $aClean = $as['clean_sheet']['total'] ?? null;
            $hBlank = $hs['failed_to_score']['total'] ?? null;
            $aBlank = $as['failed_to_score']['total'] ?? null;
            if ($hClean !== null && $aClean !== null && $hBlank !== null && $aBlank !== null) {
                $text .= sprintf(
                    ' %s have kept %d clean sheet%s and failed to score %d time%s; %s have %d clean sheet%s and %d blank%s.',
                    $home, $hClean, $hClean == 1 ? '' : 's', $hBlank, $hBlank == 1 ? '' : 's',
                    $away, $aClean, $aClean == 1 ? '' : 's', $aBlank, $aBlank == 1 ? '' : 's'
                );
            }
            $paragraphs[] = $text;
        }

        // 4. Recent scoring trend (last results with real scores).
        $trend = [];
        foreach ([[$home, $ctx['home_recent'] ?? [], $ctx['home_team_id'] ?? null], [$away, $ctx['away_recent'] ?? [], $ctx['away_team_id'] ?? null]] as [$name, $recent, $teamId]) {
            $t = $this->recentTotals($recent, $teamId);
            if ($t['played'] >= 3) {
                $trend[] = sprintf(
                    '%s have scored %d and conceded %d in their last %d matches, with %d going over 2.5 goals and both teams scoring in %d',
                    $name, $t['for'], $t['against'], $t['played'], $t['over25'], $t['btts']
                );
            }
        }
        if (! empty($trend)) {
            $paragraphs[] = implode('. ', $trend).'.';
        }

        return $paragraphs;
    }

    /**
     * Rows for a team's "recent results" table, most recent first.
     *
     * @return array<int, array{date: ?string, opponent: ?string, venue: string, score: string, result: ?string, competition: ?string}>
     */
    public function recentRows(array $recent, $teamId): array
    {
        $rows = [];
        foreach ($recent as $match) {
            $hg = $match['goals']['home'] ?? null;
            $ag = $match['goals']['away'] ?? null;
            $isHome = ($match['teams']['home']['id'] ?? null) == $teamId;
            $for = $isHome ? $hg : $ag;
            $against = $isHome ? $ag : $hg;
            $result = null;
            if ($for !== null && $against !== null) {
                $result = $for > $against ? 'W' : ($for < $against ? 'L' : 'D');
            }

            $rows[] = [
                'date' => ! empty($match['fixture']['date']) ? Carbon::parse($match['fixture']['date'])->format('M d, Y') : null,
                'opponent' => $isHome ? ($match['teams']['away']['name'] ?? null) : ($match['teams']['home']['name'] ?? null),
                'venue' => $isHome ? 'H' : 'A',
                'score' => ($hg ?? '-').'-'.($ag ?? '-'),
                'result' => $result,
                'competition' => $match['league']['name'] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * One-sentence head-to-head record, attributing wins by team id (the two
     * sides swap home and away between meetings).
     */
    public function h2hSummary(array $h2h, $homeTeamId, $awayTeamId, string $home, string $away): ?string
    {
        $homeWins = 0;
        $awayWins = 0;
        $draws = 0;
        $count = 0;
        $latest = null;

        foreach ($h2h as $match) {
            $hg = $match['goals']['home'] ?? null;
            $ag = $match['goals']['away'] ?? null;
            if ($hg === null || $ag === null) {
                continue;
            }
            $count++;
            $latest ??= $match;

            if ($hg == $ag) {
                $draws++;

                continue;
            }
            $winnerId = $hg > $ag ? ($match['teams']['home']['id'] ?? null) : ($match['teams']['away']['id'] ?? null);
            if ($winnerId == $homeTeamId) {
                $homeWins++;
            } elseif ($winnerId == $awayTeamId) {
                $awayWins++;
            }
        }

        if ($count === 0) {
            return null;
        }

        $text = sprintf(
            'In the last %d meeting%s, %s have won %d, %s have won %d and %d ended level.',
            $count, $count === 1 ? '' : 's', $home, $homeWins, $away, $awayWins, $draws
        );

        if ($latest && ! empty($latest['fixture']['date'])) {
            $text .= sprintf(
                ' The most recent, on %s, finished %s %s-%s %s.',
                Carbon::parse($latest['fixture']['date'])->format('j F Y'),
                $latest['teams']['home']['name'] ?? '',
                $latest['goals']['home'],
                $latest['goals']['away'],
                $latest['teams']['away']['name'] ?? ''
            );
        }

        return $text;
    }

    /**
     * Compares the bookmaker's implied probability with ours.
     */
    public function oddsValue(Prediction $prediction): ?string
    {
        $odds = (float) $prediction->odds;
        $probability = $prediction->probability !== null ? (float) $prediction->probability : null;

        if ($odds <= 1 || $probability === null || $probability <= 0) {
            return null;
        }

        $implied = round(100 / $odds, 1);
        $diff = round($probability - $implied, 1);
        $oddsText = rtrim(rtrim(number_format($odds, 2, '.', ''), '0'), '.');
        $probText = rtrim(rtrim((string) $probability, '0'), '.');

        if ($diff >= 3) {
            return "Odds of {$oddsText} imply a {$implied}% chance, below our {$probText}% estimate: the price looks generous by about {$diff} percentage points.";
        }
        if ($diff <= -3) {
            return "Odds of {$oddsText} imply a {$implied}% chance, a little above our {$probText}% estimate, so bookmakers rate this outcome slightly more likely than our model does.";
        }

        return "Odds of {$oddsText} imply a {$implied}% chance, in line with our {$probText}% estimate.";
    }

    /**
     * Market-specific evidence for the tip, from season stats, recent results
     * and head-to-head. Returns only the points the data supports.
     *
     * @return string[]
     */
    public function whyThisTip(Prediction $prediction, array $ctx = []): array
    {
        $home = $ctx['home_team'] ?? $prediction->home_team;
        $away = $ctx['away_team'] ?? $prediction->away_team;
        $hs = is_array($ctx['home_stats'] ?? null) ? $ctx['home_stats'] : null;
        $as = is_array($ctx['away_stats'] ?? null) ? $ctx['away_stats'] : null;
        $hRecent = $this->recentTotals($ctx['home_recent'] ?? [], $ctx['home_team_id'] ?? null);
        $aRecent = $this->recentTotals($ctx['away_recent'] ?? [], $ctx['away_team_id'] ?? null);
        $h2h = $this->h2hStats($ctx['h2h'] ?? []);
        $type = $this->classify((string) $prediction->tip);
        $points = [];

        switch ($type['type']) {
            case 'over':
            case 'under':
                $line = (float) $type['line'];
                if ($hs && $as) {
                    $hAvg = (float) ($hs['goals']['for']['average']['total'] ?? 0) + (float) ($hs['goals']['against']['average']['total'] ?? 0);
                    $aAvg = (float) ($as['goals']['for']['average']['total'] ?? 0) + (float) ($as['goals']['against']['average']['total'] ?? 0);
                    if ($hAvg > 0 && $aAvg > 0) {
                        $points[] = sprintf('%s matches average %.1f total goals this season and %s matches %.1f.', $home, $hAvg, $away, $aAvg);
                    }
                }
                foreach ([[$home, $ctx['home_recent'] ?? [], $hRecent], [$away, $ctx['away_recent'] ?? [], $aRecent]] as [$name, $recent, $t]) {
                    if ($t['played'] >= 3) {
                        $over = $this->countOver($recent, $line);
                        $points[] = sprintf('%d of %s last %d matches went over %s goals.', $over, $this->possessive($name), $t['played'], $type['line']);
                    }
                }
                if ($h2h['count'] > 0) {
                    $points[] = sprintf('The last %d head-to-head meetings averaged %.1f goals.', $h2h['count'], $h2h['goals'] / $h2h['count']);
                }
                break;

            case 'btts':
            case 'ng':
                if ($hs && $as && isset($hs['failed_to_score']['total'], $as['failed_to_score']['total'])) {
                    $points[] = sprintf(
                        '%s have failed to score in %d of %d games and %s in %d of %d.',
                        $home, $hs['failed_to_score']['total'], $hs['fixtures']['played']['total'] ?? 0,
                        $away, $as['failed_to_score']['total'], $as['fixtures']['played']['total'] ?? 0
                    );
                }
                if ($hs && $as && isset($hs['clean_sheet']['total'], $as['clean_sheet']['total'])) {
                    $points[] = sprintf('Clean sheets this season: %s %d, %s %d.', $home, $hs['clean_sheet']['total'], $away, $as['clean_sheet']['total']);
                }
                foreach ([[$home, $hRecent], [$away, $aRecent]] as [$name, $t]) {
                    if ($t['played'] >= 3) {
                        $points[] = sprintf('Both teams scored in %d of %s last %d matches.', $t['btts'], $this->possessive($name), $t['played']);
                    }
                }
                if ($h2h['count'] > 0) {
                    $points[] = sprintf('Both sides scored in %d of the last %d head-to-head meetings.', $h2h['both_scored'], $h2h['count']);
                }
                break;

            case 'home':
            case 'dc_home':
            case 'away':
            case 'dc_away':
            case 'dc_nodraw':
            case 'draw':
                if ($hs && isset($hs['fixtures']['wins']['home'], $hs['fixtures']['played']['home'])) {
                    $points[] = sprintf(
                        '%s at home: %d wins, %d draws and %d defeats from %d games.',
                        $home, $hs['fixtures']['wins']['home'], $hs['fixtures']['draws']['home'] ?? 0,
                        $hs['fixtures']['loses']['home'] ?? 0, $hs['fixtures']['played']['home']
                    );
                }
                if ($as && isset($as['fixtures']['wins']['away'], $as['fixtures']['played']['away'])) {
                    $points[] = sprintf(
                        '%s away from home: %d wins, %d draws and %d defeats from %d games.',
                        $away, $as['fixtures']['wins']['away'], $as['fixtures']['draws']['away'] ?? 0,
                        $as['fixtures']['loses']['away'] ?? 0, $as['fixtures']['played']['away']
                    );
                }
                foreach ([[$home, $hRecent], [$away, $aRecent]] as [$name, $t]) {
                    if ($t['played'] >= 3) {
                        $points[] = sprintf('%s have taken %d points from their last %d matches.', $name, $t['points'], $t['played']);
                    }
                }
                break;

            case 'cs':
                if ($hs && $as) {
                    $hFor = $hs['goals']['for']['average']['home'] ?? null;
                    $aAgainst = $as['goals']['against']['average']['away'] ?? null;
                    $aFor = $as['goals']['for']['average']['away'] ?? null;
                    $hAgainst = $hs['goals']['against']['average']['home'] ?? null;
                    if ($hFor !== null && $aAgainst !== null && $aFor !== null && $hAgainst !== null) {
                        $points[] = sprintf('%s score %s per home game and %s concede %s per away game.', $home, $hFor, $away, $aAgainst);
                        $points[] = sprintf('%s score %s per away game and %s concede %s at home.', $away, $aFor, $home, $hAgainst);
                    }
                }
                if ($h2h['count'] > 0) {
                    $points[] = sprintf('The last %d head-to-head meetings averaged %.1f goals.', $h2h['count'], $h2h['goals'] / $h2h['count']);
                }
                break;
        }

        return array_slice($points, 0, 5);
    }

    /**
     * Visible FAQ answering the questions people search for this match.
     *
     * @return array<int, array{q: string, a: string}>
     */
    public function faq(Prediction $prediction, array $fixture, array $ctx = [], ?string $result = null): array
    {
        $home = $ctx['home_team'] ?? $prediction->home_team;
        $away = $ctx['away_team'] ?? $prediction->away_team;
        $match = "{$home} vs {$away}";
        $faq = [];

        $answer = sprintf('Our tip is %s (%s)', $prediction->tip, $prediction->category);
        if ($prediction->probability !== null) {
            $answer .= ', with a probability of '.rtrim(rtrim((string) $prediction->probability, '0'), '.').'%';
        }
        if ($prediction->odds) {
            $answer .= ' at odds of '.rtrim(rtrim((string) $prediction->odds, '0'), '.');
        }
        $faq[] = ['q' => "What is the {$match} prediction?", 'a' => $answer.'.'];

        $kickoff = $this->kickoff($fixture, $prediction);
        if ($kickoff) {
            $a = $kickoff->format('l j F Y').', kick-off '.$this->clock($kickoff);
            $league = $fixture['league']['name'] ?? $prediction->league_name;
            if ($league) {
                $a .= ', in the '.$league;
            }
            if (! empty($fixture['fixture']['venue']['name'])) {
                $a .= ', at '.$fixture['fixture']['venue']['name'];
            }
            $faq[] = ['q' => "When is {$match}?", 'a' => $a.'.'];
        }

        $h2h = $this->h2hSummary($ctx['h2h'] ?? [], $ctx['home_team_id'] ?? null, $ctx['away_team_id'] ?? null, $home, $away);
        if ($h2h) {
            $faq[] = ['q' => "What is the head-to-head record between {$home} and {$away}?", 'a' => $h2h];
        }

        $hg = $fixture['goals']['home'] ?? $prediction->home_score;
        $ag = $fixture['goals']['away'] ?? $prediction->away_score;
        if (in_array($result, ['won', 'lost'], true) && $hg !== null && $ag !== null) {
            $faq[] = [
                'q' => "What was the final score of {$match}?",
                'a' => sprintf('%s %s-%s %s. Our %s tip %s.', $home, $hg, $ag, $away, $prediction->tip, $result),
            ];
        }

        return $faq;
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

    /**
     * Goals and patterns across a team's recent results.
     */
    private function recentTotals(array $recent, $teamId): array
    {
        $t = ['played' => 0, 'for' => 0, 'against' => 0, 'over25' => 0, 'btts' => 0, 'points' => 0];

        foreach ($recent as $match) {
            $hg = $match['goals']['home'] ?? null;
            $ag = $match['goals']['away'] ?? null;
            if ($hg === null || $ag === null) {
                continue;
            }
            $isHome = ($match['teams']['home']['id'] ?? null) == $teamId;
            $for = (int) ($isHome ? $hg : $ag);
            $against = (int) ($isHome ? $ag : $hg);

            $t['played']++;
            $t['for'] += $for;
            $t['against'] += $against;
            $t['over25'] += ($for + $against) > 2.5 ? 1 : 0;
            $t['btts'] += ($for > 0 && $against > 0) ? 1 : 0;
            $t['points'] += $for > $against ? 3 : ($for === $against ? 1 : 0);
        }

        return $t;
    }

    private function countOver(array $recent, float $line): int
    {
        return count(array_filter($recent, function ($match) use ($line) {
            $hg = $match['goals']['home'] ?? null;
            $ag = $match['goals']['away'] ?? null;

            return $hg !== null && $ag !== null && ($hg + $ag) > $line;
        }));
    }

    private function clock(Carbon $time): string
    {
        return $time->format('H:i').' '.($time->getOffset() === 0 ? 'UTC' : $time->format('T'));
    }

    private function possessive(string $name): string
    {
        return $name.(str_ends_with(mb_strtolower($name), 's') ? "'" : "'s");
    }

    private function kickoff(array $fixture, Prediction $prediction): ?Carbon
    {
        try {
            if (! empty($fixture['fixture']['date'])) {
                // Same clock as the match header (provider time, normally UTC)
                return Carbon::parse($fixture['fixture']['date']);
            }
            if ($prediction->match_date) {
                $date = Carbon::parse($prediction->match_date)->format('Y-m-d');

                return Carbon::parse($date.' '.($prediction->match_time ?: '00:00'));
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
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
