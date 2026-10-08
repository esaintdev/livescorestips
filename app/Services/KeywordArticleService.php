<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Prediction;
use Illuminate\Support\Str;

class KeywordArticleService
{
    /**
     * Generate (or refresh) one blog article per keyword definition.
     * Returns the number of articles created/updated.
     */
    public function generate(): int
    {
        $keywords = config('keyword-articles', []);
        $count = 0;

        foreach ($keywords as $kw) {
            if (empty($kw['title']) || empty($kw['slug'])) {
                continue;
            }

            $predictions = $this->fetchPredictions($kw['category'] ?? null);

            $content = $this->buildContent($kw, $predictions);

            Article::updateOrCreate(
                ['slug' => $kw['slug']],
                [
                    'title'            => $kw['title'],
                    'content'          => $content,
                    'meta_description' => $this->buildMetaDescription($kw, $predictions),
                    'meta_keywords'    => $this->buildKeywords($kw),
                    'published_at'     => now(),
                ]
            );

            $count++;
        }

        return $count;
    }

    /**
     * Fetch the predictions used to populate an article.
     */
    protected function fetchPredictions(?string $category): \Illuminate\Support\Collection
    {
        $query = Prediction::whereDate('match_date', '>=', now()->format('Y-m-d'))
            ->whereNotNull('tip');

        if ($category === 'banker') {
            $query->where('is_banker', true);
        } elseif ($category !== null) {
            $query->where('category', $category);
        }

        return $query->orderBy('match_date', 'asc')
            ->orderBy('match_time', 'asc')
            ->take(15)
            ->get();
    }

    /**
     * Assemble the article body HTML.
     */
    protected function buildContent(array $kw, \Illuminate\Support\Collection $predictions): string
    {
        $html = [];

        $html[] = '<p>' . e($kw['intro']) . '</p>';

        if ($predictions->isEmpty()) {
            $html[] = '<p>There are no matches available to preview right now. Check back soon for updated ' . e(strtolower($kw['title'])) . '.</p>';
        } else {
            $html[] = '<h2>Today\'s ' . e($kw['title']) . '</h2>';
            $html[] = '<ul>';
            foreach ($predictions as $p) {
                $parts = [];
                $parts[] = '<strong>' . e($p->home_team) . ' vs ' . e($p->away_team) . '</strong>';
                if ($p->league_name) {
                    $parts[] = e($p->league_name);
                }
                if ($p->match_time) {
                    $parts[] = e(\Carbon\Carbon::parse($p->match_time)->format('H:i'));
                }
                if ($p->tip) {
                    $parts[] = 'Tip: ' . e($p->tip);
                }
                if ($p->odds !== null) {
                    $parts[] = 'Odds: ' . e($p->odds);
                }
                if ($p->probability !== null) {
                    $parts[] = 'Probability: ' . e($p->probability) . '%';
                }
                $html[] = '<li>' . implode(' — ', $parts) . '</li>';
            }
            $html[] = '</ul>';
        }

        if (!empty($kw['explanation'])) {
            $html[] = '<p>' . e($kw['explanation']) . '</p>';
        }

        return implode("\n", $html);
    }

    protected function buildMetaDescription(array $kw, \Illuminate\Support\Collection $predictions): string
    {
        $base = $kw['intro'];
        if ($predictions->isNotEmpty()) {
            $first = $predictions->first();
            $base .= ' Featured: ' . $first->home_team . ' vs ' . $first->away_team . '.';
        }
        return Str::limit($base, 160, '');
    }

    protected function buildKeywords(array $kw): string
    {
        return Str::lower($kw['title']) . ', football predictions, betting tips, football tips, match analysis';
    }
}
