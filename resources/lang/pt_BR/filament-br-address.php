<?php

return [
    'fields' => [
        'street' => 'Logradouro',
        'number' => 'Numero',
        'city' => 'Cidade',
        'ibge' => 'IBGE',
        'state' => 'UF',
        'zip_code' => 'CEP',
        'neighborhood' => 'Bairro',
        'country' => 'Pais',
        'lat' => 'Latitude',
        'lng' => 'Longitude',
        'type' => 'Tipo',
        'is_default' => 'Principal',
        'complement' => 'Complemento',
    ],

    'address_types' => [
        'home' => 'Residencial',
        'work' => 'Comercial',
        'billing' => 'Cobranca',
        'shipping' => 'Entrega',
    ],

    'notifications' => [
        'address_found' => 'Endereco localizado',
        'zip_code_not_found' => 'CEP nao encontrado',
    ],

    'map' => [
        'title' => 'Mapa de localizacao',
        'label' => 'Localizacao',
        'description' => 'O mapa e atualizado pelo endereco. Voce tambem pode clicar no mapa ou arrastar o marcador para ajustar as coordenadas salvas.',
        'loading' => 'Localizando',
        'boot_error' => 'Falha ao iniciar o mapa.',
        'geocode_error' => 'Falha ao localizar este endereco.',
        'missing_mapbox_token' => 'Token do Mapbox nao configurado.',
        'missing_google_key' => 'Chave do Google Maps nao configurada.',
        'restart' => 'Recomecar',
        'address_not_found' => 'Endereco nao encontrado',
        'insufficient_address' => 'Endereco insuficiente.',
    ],

    'loading_overlay' => [
        'title' => 'Localizando endereco',
        'description' => 'Sincronizando dados...',
    ],
];
