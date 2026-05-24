<?php

use A21ns1g4ts\FilamentBrAddress\Filament\Forms\AddressForm;
use A21ns1g4ts\FilamentBrAddress\Filament\Tables\AddressTable;
use A21ns1g4ts\FilamentBrAddress\Models\Address;

it('normalizes brazilian zip codes', function () {
    expect(Address::normalizeZipCode('78.000-000'))->toBe('78000000')
        ->and(AddressForm::normalizeZipCode('78 000-000'))->toBe('78000000');
});

it('exposes the address form schema', function () {
    $schema = AddressForm::configure();

    expect($schema)
        ->toBeArray()
        ->and(hasAddressMap($schema))
        ->toBeTrue();
});

it('passes map provider credentials to the map field', function () {
    $schema = AddressForm::configure(
        mapProvider: 'google',
        mapAccessToken: 'mapbox-token',
        mapApiKey: 'google-key',
    );

    $map = findAddressMap($schema);

    expect($map)
        ->not->toBeNull()
        ->and($map->getProvider())->toBe('google')
        ->and($map->getAccessToken())->toBe('mapbox-token')
        ->and($map->getApiKey())->toBe('google-key');
});

it('can disable the map field in the address form schema', function () {
    $schema = AddressForm::configure(withMap: false);

    expect(hasAddressMap($schema))
        ->toBeFalse();
});

it('exposes default table columns and filters', function () {
    expect(AddressTable::getDefaultColumns())
        ->toBeArray()
        ->not->toBeEmpty()
        ->and(AddressTable::getDefaultFilters())
        ->toBeArray()
        ->not->toBeEmpty();
});
