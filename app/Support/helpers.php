<?php

if (! function_exists('asset_v')) {
    /**
     * Versioned asset URL using the file's modification time.
     *
     * Unlike `?v={{ time() }}`, this value is stable between requests and only
     * changes when the asset actually changes, so browsers and CDNs can cache
     * CSS/JS instead of re-downloading them on every page view.
     */
    function asset_v(string $path): string
    {
        $full = base_path($path);

        if (! is_file($full)) {
            $full = public_path($path);
        }

        $version = is_file($full) ? filemtime($full) : null;

        return asset($path).($version ? '?v='.$version : '');
    }
}

if (! function_exists('meta_description')) {
    /**
     * Clamp a meta description to the ~160 character limit used by search
     * engines, truncating on a word boundary. Text that already fits is
     * returned unchanged.
     */
    function meta_description(?string $text, int $max = 160): string
    {
        // Decode entities first: @section('description', ...) already escapes,
        // and the layout escapes again, which would show "&#039;" in snippets.
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/', ' ', $text));

        if ($text === '' || mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max);
        $lastSpace = mb_strrpos($cut, ' ');

        // Only break on a word boundary if it does not cut off too much.
        if ($lastSpace !== false && $lastSpace >= ($max - 30)) {
            $cut = mb_substr($cut, 0, $lastSpace);
        }

        return rtrim($cut, " \t\n\r\0\x0B.,;:-");
    }
}

if (! function_exists('league_is_indexable')) {
    /**
     * Whether match pages of this league should be indexed by search engines
     * (config/predictions.php "indexable_leagues" plus admin priority leagues).
     */
    function league_is_indexable($leagueId): bool
    {
        static $ids = null;

        if ($ids === null) {
            $ids = array_map('intval', config('predictions.indexable_leagues', []));
            try {
                $priority = \App\Models\Setting::get('priority_leagues', '');
                foreach (array_filter(array_map('trim', explode(',', (string) $priority))) as $id) {
                    $ids[] = (int) $id;
                }
            } catch (\Throwable $e) {
                // Settings table unavailable (install/tests): config list only
            }
            $ids = array_flip($ids);
        }

        return $leagueId !== null && isset($ids[(int) $leagueId]);
    }
}
