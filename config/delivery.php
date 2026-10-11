<?php

return [

    /*
    | Single source for per-kilometre delivery pricing (JS reads this too).
    */
    'cost_per_km' => 1.00,

    'origin' => 'H1, 10, 68159 Mannheim, Germany',

    'free_city' => 'Mannheim',

    'free_city_tokens' => [
        'mannheim',
    ],

    /*
    | Stadt Mannheim PLZ (Quadrate, Neckarau, Rheinau, Seckenheim, Wallstadt, Käfertal, …).
    */
    'free_city_plz_pattern' => '/\b(68159|6816[13579]|68199|68219|68229|68239|68259|6830[579])\b/',

    /*
    | Backward-compatible alias used by older code paths.
    */
    'stuttgart_plz_pattern' => '/\b(68159|6816[13579]|68199|68219|68229|68239|68259|6830[579])\b/',

    /*
    | If Distance Matrix fails, JS treats these tokens in the typed address as free.
    */
    'error_free_tokens' => [
        'mannheim',
    ],

];
