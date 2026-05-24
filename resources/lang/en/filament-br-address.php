<?php

return [
    'fields' => [
        'street' => 'Street',
        'number' => 'Number',
        'city' => 'City',
        'ibge' => 'IBGE',
        'state' => 'State',
        'zip_code' => 'ZIP code',
        'neighborhood' => 'Neighborhood',
        'country' => 'Country',
        'lat' => 'Latitude',
        'lng' => 'Longitude',
        'type' => 'Type',
        'is_default' => 'Default',
        'complement' => 'Complement',
    ],

    'address_types' => [
        'home' => 'Home',
        'work' => 'Work',
        'billing' => 'Billing',
        'shipping' => 'Shipping',
    ],

    'notifications' => [
        'address_found' => 'Address found',
        'zip_code_not_found' => 'ZIP code not found',
    ],

    'map' => [
        'title' => 'Location map',
        'label' => 'Location',
        'description' => 'The map updates from the address. You can also click the map or drag the marker to fine tune the saved coordinates.',
        'loading' => 'Locating',
        'boot_error' => 'Failed to start the map.',
        'geocode_error' => 'Failed to locate this address.',
        'missing_mapbox_token' => 'Mapbox token is not configured.',
        'missing_google_key' => 'Google Maps API key is not configured.',
        'restart' => 'Restart',
        'address_not_found' => 'Address not found',
        'insufficient_address' => 'Insufficient address.',
    ],

    'loading_overlay' => [
        'title' => 'Locating address',
        'description' => 'Synchronizing data...',
    ],
];
