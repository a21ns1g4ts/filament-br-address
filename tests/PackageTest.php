<?php

use A21ns1g4ts\FilamentBrAddress\Facades\FilamentBrAddress as FilamentBrAddressFacade;
use A21ns1g4ts\FilamentBrAddress\FilamentBrAddress;
use A21ns1g4ts\FilamentBrAddress\FilamentBrAddressServiceProvider;

it('resolves the package facade root', function () {
    expect(FilamentBrAddressFacade::getFacadeRoot())
        ->toBeInstanceOf(FilamentBrAddress::class);
});

it('runs the package command', function () {
    $this->artisan('filament-br-address')
        ->expectsOutput('All done')
        ->assertExitCode(0);
});

it('exposes service provider package metadata', function () {
    expect(FilamentBrAddressServiceProvider::$name)
        ->toBe('filament-br-address')
        ->and(FilamentBrAddressServiceProvider::$viewNamespace)
        ->toBe('filament-br-address');
});

it('exposes facade helpers', function () {
    expect(FilamentBrAddressFacade::form())
        ->toBeArray()
        ->and(hasAddressMap(FilamentBrAddressFacade::form()))
        ->toBeTrue()
        ->and(FilamentBrAddressFacade::tableColumns())
        ->toBeArray()
        ->not->toBeEmpty()
        ->and(FilamentBrAddressFacade::tableFilters())
        ->toBeArray()
        ->not->toBeEmpty();
});
