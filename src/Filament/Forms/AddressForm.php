<?php

namespace A21ns1g4ts\FilamentBrAddress\Filament\Forms;

use A21ns1g4ts\FilamentBrAddress\Enums\AddressType;
use A21ns1g4ts\FilamentBrAddress\Forms\Components\AddressMap;
use A21ns1g4ts\FilamentBrAddress\Forms\Components\LoadingOverlay;
use A21ns1g4ts\FilamentBrAddress\Services\CepService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\RawJs;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;

class AddressForm
{
    /**
     * @return array<int, mixed>
     */
    public static function configure(
        bool $withMap = true,
        ?string $mapProvider = null,
        string | \Closure | null $mapAccessToken = null,
        string | \Closure | null $mapApiKey = null,
    ): array {
        return [
            Hidden::make('lat')->dehydrated(),
            Hidden::make('lng')->dehydrated(),

            Group::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make(10)
                        ->schema([
                            TextInput::make('zip_code')
                                ->label(__('filament-br-address::filament-br-address.fields.zip_code'))
                                ->mask(RawJs::make('\'99999-999\''))
                                ->placeholder('00000-000')
                                ->required()
                                ->live()
                                ->columnSpan(2)
                                ->afterStateUpdatedJs(fn (TextInput $component) => "
                                    const target = '{$component->getContainer()->getStatePath()}.location';
                                    const zipCode = \$state ? \$state.replace(/\D/g, '') : '';

                                    window.filamentBrAddressReadyMap = window.filamentBrAddressReadyMap || {};
                                    if (typeof window.filamentBrAddressReadyMap[target] === 'undefined') {
                                        window.filamentBrAddressReadyMap[target] = false;
                                        setTimeout(() => { window.filamentBrAddressReadyMap[target] = true; }, 1000);
                                    }

                                    if (typeof \$el.filamentBrAddressLastZipCodeSearch === 'undefined') {
                                        \$el.filamentBrAddressLastZipCodeSearch = zipCode;
                                    }

                                    if (
                                        window.filamentBrAddressReadyMap[target] &&
                                        zipCode.length === 8 &&
                                        \$el.filamentBrAddressLastZipCodeSearch !== zipCode
                                    ) {
                                        \$el.filamentBrAddressLastZipCodeSearch = zipCode;
                                        \$dispatch('filament-br-address-start-zip-code-loading', { target });
                                    } else {
                                        \$el.filamentBrAddressLastZipCodeSearch = zipCode;
                                    }
                                ")
                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old, Component $livewire, TextInput $component): void {
                                    $zipCode = static::normalizeZipCode($state);
                                    $oldZipCode = static::normalizeZipCode($old);
                                    $target = $component->getContainer()->getStatePath() . '.location';

                                    if (strlen($zipCode) !== 8 || $zipCode === $oldZipCode) {
                                        $livewire->dispatch('filament-br-address-stop-zip-code-loading', target: $target);

                                        return;
                                    }

                                    static::searchZipCode($zipCode, $set, $get, $livewire, $target);
                                }),

                            TextInput::make('country')
                                ->label(__('filament-br-address::filament-br-address.fields.country'))
                                ->default('Brasil')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Component $livewire): void {
                                    static::refreshMap($get, $livewire);
                                })
                                ->columnSpan(2),

                            TextInput::make('state')
                                ->label(__('filament-br-address::filament-br-address.fields.state'))
                                ->required()
                                ->maxLength(2)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get, Component $livewire, ?string $state): void {
                                    $set('state', Str::upper((string) $state));
                                    static::refreshMap($get, $livewire);
                                })
                                ->columnSpan(1),

                            TextInput::make('city')
                                ->label(__('filament-br-address::filament-br-address.fields.city'))
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Component $livewire): void {
                                    static::refreshMap($get, $livewire);
                                })
                                ->columnSpan(3),

                            TextInput::make('ibge')
                                ->label(__('filament-br-address::filament-br-address.fields.ibge'))
                                ->columnSpan(2),
                        ]),

                    Grid::make(8)
                        ->schema([
                            TextInput::make('street')
                                ->label(__('filament-br-address::filament-br-address.fields.street'))
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Component $livewire): void {
                                    static::refreshMap($get, $livewire);
                                })
                                ->columnSpan(5),

                            TextInput::make('neighborhood')
                                ->label(__('filament-br-address::filament-br-address.fields.neighborhood'))
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Component $livewire): void {
                                    static::refreshMap($get, $livewire);
                                })
                                ->columnSpan(3),
                        ]),

                    Grid::make(8)
                        ->schema([
                            TextInput::make('number')
                                ->label(__('filament-br-address::filament-br-address.fields.number'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(999999999)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Component $livewire): void {
                                    static::refreshMap($get, $livewire);
                                })
                                ->columnSpan(2),

                            TextInput::make('complement')
                                ->label(__('filament-br-address::filament-br-address.fields.complement'))
                                ->maxLength(255)
                                ->columnSpan(6),
                        ]),
                    Grid::make(8)
                        ->schema([
                            ToggleButtons::make('type')
                                ->label(__('filament-br-address::filament-br-address.fields.type'))
                                ->inline()
                                ->options(AddressType::class)
                                ->default(AddressType::Home)
                                ->required()
                                ->columnSpan(7),

                            Toggle::make('is_default')
                                ->label(__('filament-br-address::filament-br-address.fields.is_default'))
                                ->default(false)
                                ->inline(false)
                                ->columnSpan(1),
                        ]),

                    ...($withMap ? [
                        Section::make(__('filament-br-address::filament-br-address.map.title'))
                            ->description(__('filament-br-address::filament-br-address.map.description'))
                            ->collapsed()
                            ->schema([
                                AddressMap::make('location')
                                    ->label(__('filament-br-address::filament-br-address.map.label'))
                                    ->provider($mapProvider)
                                    ->accessToken($mapAccessToken)
                                    ->apiKey($mapApiKey),
                            ])
                            ->columnSpanFull(),
                    ] : []),

                    LoadingOverlay::make('loading_overlay'),
                ]),
        ];
    }

    public static function searchZipCode(?string $state, Set $set, Get $get, Component $livewire, ?string $target = null): void
    {
        $zipCode = static::normalizeZipCode($state);

        if (strlen($zipCode) !== 8) {
            return;
        }

        $set('lat', null);
        $set('lng', null);

        try {
            $data = app(CepService::class)->get($zipCode);

            if (! $data || Arr::has($data, 'message') || ! Arr::get($data, 'state')) {
                Notification::make()
                    ->title(__('filament-br-address::filament-br-address.notifications.zip_code_not_found'))
                    ->danger()
                    ->send();

                return;
            }

            $street = Arr::get($data, 'street');
            $neighborhood = Arr::get($data, 'neighborhood');
            $city = Arr::get($data, 'city');
            $state = Arr::get($data, 'state');

            $set('zip_code', $zipCode);
            $set('street', $street);
            $set('neighborhood', $neighborhood);
            $set('city', $city);
            $set('state', Str::upper((string) $state));
            $set('ibge', Arr::get($data, 'city_ibge') ?? Arr::get($data, 'ibge'));
            $set('country', $get('country') ?: 'Brasil');

            Notification::make()
                ->title(__('filament-br-address::filament-br-address.notifications.address_found'))
                ->body(collect([$street, $neighborhood, "{$city}/{$state}"])->filter()->join(' - '))
                ->success()
                ->send();

            $address = collect([
                $street,
                $neighborhood,
                $city,
                $state,
                'CEP ' . $zipCode,
                $get('country') ?: 'Brasil',
            ])->filter()->join(', ');

            $livewire->dispatch('filament-br-address-geocode-address', address: $address, target: $target, showNotification: false);
        } finally {
            $livewire->dispatch('filament-br-address-stop-zip-code-loading', target: $target);
        }
    }

    public static function refreshMap(Get $get, Component $livewire, ?string $target = null, bool $force = false): void
    {
        if (! $force && $get('lat') && $get('lng')) {
            return;
        }

        $address = static::makeAddressString($get);

        if ($address === '') {
            return;
        }

        $livewire->dispatch('filament-br-address-geocode-address', address: $address, target: $target, showNotification: false);
    }

    public static function normalizeZipCode(?string $zipCode): string
    {
        return (string) preg_replace('/\D/', '', (string) $zipCode);
    }

    protected static function makeAddressString(Get $get): string
    {
        return collect([
            $get('street'),
            $get('number'),
            $get('neighborhood'),
            $get('city'),
            $get('state'),
            $get('zip_code') ? 'CEP ' . $get('zip_code') : null,
            $get('country') ?: 'Brasil',
        ])->filter()->join(', ');
    }
}
