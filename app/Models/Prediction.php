<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixture_id',
        'league_id',
        'slug',
        'league_name',
        'league_country',
        'league_logo',
        'home_team',
        'home_team_logo',
        'away_team',
        'away_team_logo',
        'home_score',
        'away_score',
        'match_date',
        'match_time',
        'home_form',
        'away_form',
        'tip',
        'category',
        'odds',
        'probability',
        'bookmaker_name',
        'bookmaker_image',
        'bookmaker_url',
        'status',
        'is_featured',
        'is_banker',
        'admin_note',
        'content_updated_at',
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'home_form' => 'array',
        'away_form' => 'array',
        'odds' => 'decimal:2',
        'probability' => 'decimal:2',
        'is_banker' => 'boolean',
        'content_updated_at' => 'datetime',
    ];

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('match_date', '>=', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('match_date', today());
    }

    public function scopeBanker($query)
    {
        return $query->where('is_banker', true);
    }

    public function scopeLiveWindow($query)
    {
        $now = now();
        $start = $now->copy()->subMinutes(120)->format('H:i:s');
        $end = $now->copy()->addMinutes(5)->format('H:i:s');

        $query->whereDate('match_date', $now->format('Y-m-d'));

        if ($start > $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereTime('match_time', '>=', $start)
                    ->orWhereTime('match_time', '<=', $end);
            });
        } else {
            $query->whereTime('match_time', '>=', $start)
                ->whereTime('match_time', '<=', $end);
        }
    }

    /**
     * Get the comments for the prediction.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Generate a slug from team names
     */
    public static function generateSlug($homeTeam, $awayTeam, $fixtureId)
    {
        $baseSlug = Str::slug($homeTeam.'-vs-'.$awayTeam);
        $slug = $baseSlug;

        // If the base slug already exists, try appending the fixture ID
        if (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$fixtureId;
        }

        // If that still exists (e.g. second prediction for same match), append a counter
        if (static::where('slug', $slug)->exists()) {
            $count = 2;
            while (static::where('slug', $slug.'-'.$count)->exists()) {
                $count++;
            }
            $slug = $slug.'-'.$count;
        }

        return $slug;
    }

    /**
     * Get formatted score display
     */
    public function getScoreDisplayAttribute()
    {
        if ($this->home_score !== null && $this->away_score !== null) {
            return $this->home_score.'-'.$this->away_score;
        }

        return null;
    }

    /**
     * Whether this record carries a real, usable tip (not a placeholder).
     */
    public function hasRealTip(): bool
    {
        $tip = trim((string) $this->tip);

        return $tip !== '' && strcasecmp($tip, 'Awaiting odds') !== 0;
    }

    /**
     * Whether the prediction has a terminal settlement status.
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, ['won', 'lost', 'void'], true);
    }

    /**
     * Resolve the stable canonical prediction record for a provider fixture.
     *
     * Multiple prediction/category records may share one fixture_id. The
     * canonical representation is the first-created record with a real tip,
     * falling back to the first record that has a slug.
     */
    public static function canonicalForFixture($fixtureId): ?self
    {
        return static::where('fixture_id', $fixtureId)
            ->whereNotNull('slug')
            ->orderByRaw("CASE WHEN tip IS NULL OR tip = '' OR tip = 'Awaiting odds' THEN 1 ELSE 0 END")
            ->orderBy('id')
            ->first();
    }

    /**
     * Get result with score if available
     */
    public function getResultDisplayAttribute()
    {
        if ($this->status === 'won' || $this->status === 'lost') {
            if ($this->score_display) {
                return ucfirst($this->status).' ('.$this->score_display.')';
            }

            return ucfirst($this->status);
        }

        return ucfirst($this->status);
    }
}
