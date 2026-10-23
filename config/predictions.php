<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maximum prediction pages per fixture (per date)
    |--------------------------------------------------------------------------
    |
    | The generator can derive several market predictions from one fixture.
    | Publishing one page per market creates many near-duplicate match URLs and
    | floods the crawl budget. This caps how many prediction records are stored
    | for a single fixture on a single date. Set a high value to disable.
    |
    */

    'max_per_fixture_per_date' => (int) env('PREDICTIONS_MAX_PER_FIXTURE', 3),

    /*
    |--------------------------------------------------------------------------
    | Indexable leagues (API-Football league ids)
    |--------------------------------------------------------------------------
    |
    | Match pages from these leagues are indexed and listed in the sitemap, and
    | fill-all generates their predictions first. Pages from other leagues stay
    | on the site for visitors but are "noindex" and left out of the sitemap,
    | so crawl budget goes to matches people actually search for. The admin
    | "priority leagues" setting is always added on top of this list.
    |
    */

    'indexable_leagues' => [
        // International & continental club competitions
        1, 2, 3, 4, 5, 6, 9, 10, 11, 12, 13, 15, 17, 29, 30, 31, 32, 34, 531, 772, 848, 960,
        // England
        39, 40, 41, 42, 43, 45, 48,
        // Spain, Italy, Germany, France
        140, 141, 143, 135, 136, 137, 78, 79, 80, 81, 61, 62, 66,
        // Rest of Europe (top flights, plus Dutch/Portuguese/Scottish second tiers)
        88, 89, 94, 95, 144, 179, 180, 185, 203, 197, 218, 207, 119, 103, 113, 106,
        345, 210, 286, 235, 333, 283, 357,
        // Americas
        253, 255, 262, 71, 72, 73, 128, 129, 130, 239, 265, 268, 242, 281, 250, 252,
        // Asia, Oceania & Africa
        307, 98, 292, 188, 169, 290, 233, 200, 288, 570,
    ],

];
