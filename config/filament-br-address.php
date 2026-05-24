<?php

use A21ns1g4ts\FilamentBrAddress\Models\Address;

return [
    'address_model' => Address::class,

    'cep' => [
        'base_url' => env('FILAMENT_BR_ADDRESS_CEP_BASE_URL', 'https://brasilapi.com.br/api'),
        'timeout' => env('FILAMENT_BR_ADDRESS_CEP_TIMEOUT', 10),
        'cache_ttl' => env('FILAMENT_BR_ADDRESS_CEP_CACHE_TTL', 86400),
    ],

    'maps' => [
        'default' => env('FILAMENT_BR_ADDRESS_MAP_PROVIDER', 'mapbox'),
        'height' => env('FILAMENT_BR_ADDRESS_MAP_HEIGHT', 400),

        'mapbox' => [
            'access_token' => env('MAPBOX_ACCESS_TOKEN'),
            'style' => env('MAPBOX_STYLE', 'mapbox://styles/mapbox/satellite-streets-v12'),
        ],

        'google' => [
            'api_key' => env('GOOGLE_MAPS_API_KEY'),
            'map_id' => env('GOOGLE_MAPS_MAP_ID'),
        ],
    ],
];
