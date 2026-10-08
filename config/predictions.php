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

];
