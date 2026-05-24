# Filament BR Address

[![Latest Version on Packagist](https://img.shields.io/packagist/v/a21ns1g4ts/filament-br-address.svg?style=flat-square)](https://packagist.org/packages/a21ns1g4ts/filament-br-address)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/a21ns1g4ts/filament-br-address/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/a21ns1g4ts/filament-br-address/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Coverage](https://img.shields.io/codecov/c/github/a21ns1g4ts/filament-br-address/main?style=flat-square)](https://codecov.io/gh/a21ns1g4ts/filament-br-address)
[![Total Downloads](https://img.shields.io/packagist/dt/a21ns1g4ts/filament-br-address.svg?style=flat-square)](https://packagist.org/packages/a21ns1g4ts/filament-br-address)

Brazilian address fields for Filament, with CEP lookup and an optional map field that ships with Mapbox and Google Maps support.

## Installation

You can install the package via composer:

```bash
composer require a21ns1g4ts/filament-br-address
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-br-address-config"
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-br-address-migrations"
php artisan migrate
```

## Configuration

Set your preferred map provider and credentials:

```env
FILAMENT_BR_ADDRESS_MAP_PROVIDER=mapbox
MAPBOX_ACCESS_TOKEN=

# or
FILAMENT_BR_ADDRESS_MAP_PROVIDER=google
GOOGLE_MAPS_API_KEY=
```

The package config also controls CEP lookup and defaults:

```php
return [
    'address_model' => \A21ns1g4ts\FilamentBrAddress\Models\Address::class,

    'cep' => [
        'base_url' => env('FILAMENT_BR_ADDRESS_CEP_BASE_URL', 'https://brasilapi.com.br/api'),
        'timeout' => env('FILAMENT_BR_ADDRESS_CEP_TIMEOUT', 10),
        'cache_ttl' => env('FILAMENT_BR_ADDRESS_CEP_CACHE_TTL', 86400),
    ],

    'maps' => [
        'default' => env('FILAMENT_BR_ADDRESS_MAP_PROVIDER', 'mapbox'),
        'height' => env('FILAMENT_BR_ADDRESS_MAP_HEIGHT', 400),
    ],
];
```

## Usage

Add the trait to any model that owns addresses:

```php
use A21ns1g4ts\FilamentBrAddress\Concerns\HasAddresses;

class Customer extends Model
{
    use HasAddresses;
}
```

Use the default Filament schema:

```php
use A21ns1g4ts\FilamentBrAddress\Filament\Forms\AddressForm;
use A21ns1g4ts\FilamentBrAddress\Filament\Tables\AddressTable;

AddressForm::configure();
AddressForm::configure(mapProvider: 'google');
AddressForm::configure(withMap: false);

AddressTable::getDefaultColumns();
AddressTable::getDefaultFilters();
```

You can also use the facade:

```php
use A21ns1g4ts\FilamentBrAddress\Facades\FilamentBrAddress;

FilamentBrAddress::form();
FilamentBrAddress::tableColumns();
FilamentBrAddress::tableFilters();
```

## Components

### `Address`

`A21ns1g4ts\FilamentBrAddress\Models\Address` is the default Eloquent model for the `addresses` table.

It includes:

- `addressable_id` and `addressable_type` morph fields.
- Brazilian address fields: `zip_code`, `street`, `number`, `neighborhood`, `city`, `state`, `ibge`, `country`.
- Map coordinates: `lat`, `lng`.
- Address classification: `type`, `is_default`, `complement`.
- Computed `location` array with `lat` and `lng`.
- Computed `full_address` string.
- CEP normalization before saving.
- Default-address guard so only one address per owner is marked as default.

### `HasAddresses`

Use `A21ns1g4ts\FilamentBrAddress\Concerns\HasAddresses` on models that own addresses:

```php
use A21ns1g4ts\FilamentBrAddress\Concerns\HasAddresses;

class Customer extends Model
{
    use HasAddresses;
}

$customer->addresses;
$customer->defaultAddress();
```

If you need a custom model, change `address_model` in the package config.

### `AddressType`

`A21ns1g4ts\FilamentBrAddress\Enums\AddressType` provides the default Filament labels for:

- `home`
- `work`
- `billing`
- `shipping`

### `AddressForm`

`A21ns1g4ts\FilamentBrAddress\Filament\Forms\AddressForm` returns a ready-to-use Filament schema:

```php
AddressForm::configure();
```

Available arguments:

```php
AddressForm::configure(
    withMap: true,
    mapProvider: 'mapbox',
    mapAccessToken: 'your-mapbox-token',
    mapApiKey: null,
);
```

For Google Maps:

```php
AddressForm::configure(
    mapProvider: 'google',
    mapApiKey: 'your-google-maps-key',
);
```

Without a map:

```php
AddressForm::configure(withMap: false);
```

The form looks up Brazilian CEPs through BrasilAPI and fills `street`, `neighborhood`, `city`, `state`, `ibge`, and `country`. When the map is enabled, it dispatches geocoding updates to the map field.

### `AddressMap`

`A21ns1g4ts\FilamentBrAddress\Forms\Components\AddressMap` is a standalone Filament field. It works with `lat` and `lng` fields in the same parent state path.

Mapbox example:

```php
use A21ns1g4ts\FilamentBrAddress\Forms\Components\AddressMap;

AddressMap::make('location')
    ->provider('mapbox')
    ->accessToken('your-mapbox-token');
```

Google Maps example:

```php
AddressMap::make('location')
    ->provider('google')
    ->apiKey('your-google-maps-key');
```

Other options:

```php
AddressMap::make('location')
    ->height(480)
    ->draggable()
    ->showControls();
```

If you do not pass keys directly, the component reads from `config('filament-br-address.maps.*')`.

Custom JavaScript providers can be registered with:

```js
window.filamentBrAddressMapProviders = window.filamentBrAddressMapProviders || {}

window.filamentBrAddressMapProviders.myProvider = (config, component) => ({
    async boot() {
        // Initialize your map provider here.
    },

    async geocodeAddress(address) {
        // Geocode and call component.updateCoordinates(lat, lng).
    },
})
```

Then use:

```php
AddressMap::make('location')
    ->provider('myProvider');
```

### `AddressTable`

`A21ns1g4ts\FilamentBrAddress\Filament\Tables\AddressTable` exposes table defaults:

```php
AddressTable::getDefaultColumns();
AddressTable::getDefaultFilters();
```

The default columns include street, number, neighborhood, city, state, ZIP code, type, and a toggle for the default address.

### `CepService`

`A21ns1g4ts\FilamentBrAddress\Services\CepService` performs CEP lookup against BrasilAPI by default:

```php
app(\A21ns1g4ts\FilamentBrAddress\Services\CepService::class)->get('78000000');
```

The service caches results using Laravel cache. You can change the base URL, timeout, and TTL in the package config.

## Testing

```bash
composer test
composer test-coverage
composer phpstan
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [a21ns1g4ts](https://github.com/a21ns1g4ts)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
