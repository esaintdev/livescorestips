<?php

namespace App\Services;

use App\Models\Prediction;
use Illuminate\Support\Facades\Log;

/**
 * Single source of truth for settling predictions.
 *
 * Used by the scheduled status updater, the match detail page and any
 * other consumer that needs to know whether a tip won, lost, was voided
 * or is still pending. Settlement logic must never be duplicated in
 * controllers or Blade templates.
 */
class PredictionResultService
{
    public const PENDING = 'pending';

    public const WON = 'won';

    public const LOST = 'lost';

    public const VOID = 'void';

    /** Provider statuses that represent a final, settleable result. */
    private const FINISHED_STATUSES = ['FT', 'AET', 'PEN'];

    /** Provider statuses where the fixture will not be played / has no result. */
    private const VOID_STATUSES = ['CANC', 'ABD', 'AWD', 'WO'];

    /**
     * Resolve the settlement status for a prediction.
     *
     * @param  string  $tip  The prediction tip (e.g. "1X", "Over 2.5").
     * @param  int|null  $homeGoals  Final home goals, if known.
     * @param  int|null  $awayGoals  Final away goals, if known.
     * @param  string|null  $fixtureStatusShort  API-Football status short code, if known.
     * @return string pending|won|lost|void
     */
    public function resolveStatus(string $tip, ?int $homeGoals, ?int $awayGoals, ?string $fixtureStatusShort = null): string
    {
        $short = $fixtureStatusShort !== null ? strtoupper(trim($fixtureStatusShort)) : null;

        if ($short !== null && in_array($short, self::VOID_STATUSES, true)) {
            return self::VOID;
        }

        // Postponed/suspended/interrupted fixtures may still be played. Do not
        // settle them either way until the provider gives a final result.
        if ($short !== null && ! in_array($short, self::FINISHED_STATUSES, true)) {
            return self::PENDING;
        }

        if ($homeGoals === null || $awayGoals === null) {
            return self::PENDING;
        }

        return $this->determineOutcome($tip, (int) $homeGoals, (int) $awayGoals) ?? self::PENDING;
    }

    /**
     * Determine whether a tip won or lost based on final scores.
     *
     * @return string|null won|lost, or null when the market is unknown.
     */
    public function determineOutcome(string $tip, int $homeGoals, int $awayGoals): ?string
    {
        $tip = strtoupper(trim($tip));

        // 1x2 Betting
        if ($tip === '1' || $tip === 'HOME' || $tip === 'HOME WIN') {
            return $homeGoals > $awayGoals ? self::WON : self::LOST;
        }
        if ($tip === '2' || $tip === 'AWAY' || $tip === 'AWAY WIN') {
            return $awayGoals > $homeGoals ? self::WON : self::LOST;
        }
        if ($tip === 'X' || $tip === 'DRAW') {
            return $homeGoals === $awayGoals ? self::WON : self::LOST;
        }

        // Double Chance
        if (str_starts_with($tip, '1X')) {
            return $homeGoals >= $awayGoals ? self::WON : self::LOST;
        }
        if (str_starts_with($tip, 'X2')) {
            return $awayGoals >= $homeGoals ? self::WON : self::LOST;
        }
        if (str_starts_with($tip, '12')) {
            return $homeGoals !== $awayGoals ? self::WON : self::LOST;
        }

        // Over/Under Goals (e.g. "Over 2.5", "Under 3.5", "Over 2.5 Goals")
        if (preg_match('/(OVER|UNDER)\s+(\d+(\.\d+)?)/i', $tip, $matches)) {
            $type = strtoupper($matches[1]);
            $threshold = floatval($matches[2]);
            $totalGoals = $homeGoals + $awayGoals;

            if ($type === 'OVER') {
                return $totalGoals > $threshold ? self::WON : self::LOST;
            }
            if ($type === 'UNDER') {
                return $totalGoals < $threshold ? self::WON : self::LOST;
            }
        }

        // BTTS (Both Teams To Score)
        if ($tip === 'BTTS' || $tip === 'GG' || str_contains($tip, 'BTTS') || str_contains($tip, 'BOTH TEAMS')) {
            return ($homeGoals > 0 && $awayGoals > 0) ? self::WON : self::LOST;
        }

        // NG (No Goal)
        if ($tip === 'NG' || $tip === 'NO GG' || str_contains($tip, 'NO GOAL')) {
            return ($homeGoals === 0 || $awayGoals === 0) ? self::WON : self::LOST;
        }

        // Correct Score (e.g. "2-1")
        if (preg_match('/^(\d+)-(\d+)$/', $tip, $matches)) {
            $predictedHome = intval($matches[1]);
            $predictedAway = intval($matches[2]);

            return ($homeGoals === $predictedHome && $awayGoals === $predictedAway) ? self::WON : self::LOST;
        }

        Log::warning("Unknown tip format: {$tip}");

        return null;
    }

    /**
     * Human-readable label for a terminal status.
     */
    public function label(?string $status): string
    {
        return strtoupper((string) $status);
    }

    /**
     * Deterministic post-match summary derived from the original prediction,
     * the final score and the settlement result.
     */
    public function summary(Prediction $prediction, string $result, ?int $homeGoals, ?int $awayGoals): string
    {
        $home = $prediction->home_team;
        $away = $prediction->away_team;
        $tip = trim((string) $prediction->tip);
        $score = ($homeGoals !== null && $awayGoals !== null) ? "{$homeGoals}-{$awayGoals}" : null;
        $total = ($homeGoals !== null && $awayGoals !== null) ? $homeGoals + $awayGoals : null;

        if ($result === self::VOID) {
            return "The fixture between {$home} and {$away} did not produce a settleable result, so the {$tip} prediction was voided.";
        }

        if ($score === null) {
            return "The {$tip} prediction for {$home} vs {$away} is awaiting a final result.";
        }

        $landed = $result === self::WON;
        $verb = $landed ? 'landed successfully' : 'did not land';
        $tipUpper = strtoupper($tip);

        if (preg_match('/(OVER|UNDER)\s+(\d+(\.\d+)?)/i', $tip, $m)) {
            $type = ucfirst(strtolower($m[1]));
            $line = $m[2];

            return "The match finished {$score}, producing {$total} total goals. The {$type} {$line} prediction therefore {$verb}.";
        }

        if ($tipUpper === 'BTTS' || $tipUpper === 'GG') {
            $both = ($homeGoals > 0 && $awayGoals > 0);
            $detail = $both ? 'both teams scored' : 'both teams did not score';

            return "The match finished {$score}, and {$detail}, so the BTTS prediction {$verb}.";
        }

        if ($tipUpper === 'NG' || $tipUpper === 'NO GG') {
            $oneBlank = ($homeGoals === 0 || $awayGoals === 0);
            $detail = $oneBlank ? 'one team failed to score' : 'both teams scored';

            return "The match finished {$score}, and {$detail}, so the NG prediction {$verb}.";
        }

        if (preg_match('/^(\d+)-(\d+)$/', $tip)) {
            return "The match finished {$score}, and the predicted correct score {$tip} {$verb}.";
        }

        if ($tipUpper === 'X' || $tipUpper === 'DRAW') {
            return "The match finished {$score}. The draw prediction {$verb}.";
        }

        if ($tipUpper === '1' || $tipUpper === 'HOME' || $tipUpper === 'HOME WIN') {
            return "The match finished {$score}, with {$home} winning, so the home win prediction {$verb}.";
        }

        if ($tipUpper === '2' || $tipUpper === 'AWAY' || $tipUpper === 'AWAY WIN') {
            return "The match finished {$score}, with {$away} winning, so the away win prediction {$verb}.";
        }

        if (str_starts_with($tipUpper, '1X')) {
            return "The match finished {$score}. The 1X (home or draw) prediction {$verb}.";
        }
        if (str_starts_with($tipUpper, 'X2')) {
            return "The match finished {$score}. The X2 (away or draw) prediction {$verb}.";
        }
        if (str_starts_with($tipUpper, '12')) {
            return "The match finished {$score}. The 12 (home or away) prediction {$verb}.";
        }

        return "The match finished {$score}. The {$tip} prediction {$verb}.";
    }
}
