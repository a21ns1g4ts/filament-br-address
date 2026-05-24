<?php

namespace A21ns1g4ts\FilamentBrAddress\Models;

use A21ns1g4ts\FilamentBrAddress\Enums\AddressType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int|null $addressable_id
 * @property string|null $addressable_type
 * @property string|null $street
 * @property int|null $number
 * @property string|null $city
 * @property string|null $ibge
 * @property string|null $state
 * @property string|null $zip_code
 * @property string|null $neighborhood
 * @property string|null $country
 * @property float|null $lat
 * @property float|null $lng
 * @property AddressType|string|null $type
 * @property bool $is_default
 * @property string|null $complement
 * @property-read Model|null $addressable
 * @property-read array{lat: float|null, lng: float|null} $location
 * @property-read string $full_address
 */
class Address extends Model
{
    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'street',
        'number',
        'city',
        'ibge',
        'state',
        'zip_code',
        'neighborhood',
        'country',
        'lat',
        'lng',
        'type',
        'is_default',
        'complement',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'number' => 'integer',
        'lat' => 'float',
        'lng' => 'float',
        'type' => AddressType::class,
    ];

    protected $appends = [
        'location',
        'full_address',
    ];

    protected static function booted(): void
    {
        static::saving(function (Address $address): void {
            $address->zip_code = static::normalizeZipCode($address->zip_code);

            $addresses = $address->addressableAddresses();

            if ($address->is_default && $addresses) {
                $addresses
                    ->whereKeyNot($address->getKey())
                    ->update(['is_default' => false]);
            }
        });

        static::saved(function (Address $address): void {
            $addresses = $address->addressableAddresses();

            if (! $addresses) {
                return;
            }

            $hasDefault = $addresses
                ->where('is_default', true)
                ->exists();

            if (! $hasDefault) {
                $addresses
                    ->oldest()
                    ->first()
                    ?->updateQuietly(['is_default' => true]);
            }
        });

        static::deleted(function (Address $address): void {
            $addresses = $address->addressableAddresses();

            if (! $addresses) {
                return;
            }

            if (! $addresses->where('is_default', true)->exists()) {
                $addresses
                    ->oldest()
                    ->first()
                    ?->updateQuietly(['is_default' => true]);
            }
        });
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function location(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): array => [
                'lat' => $attributes['lat'] ?? null,
                'lng' => $attributes['lng'] ?? null,
            ],
        );
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): string => collect([
                $attributes['street'] ?? null,
                $attributes['number'] ?? null,
                $attributes['neighborhood'] ?? null,
                $attributes['city'] ?? null,
                $attributes['state'] ?? null,
                $attributes['zip_code'] ?? null,
                $attributes['country'] ?? null,
            ])->filter()->join(', '),
        );
    }

    public static function normalizeZipCode(?string $zipCode): ?string
    {
        if ($zipCode === null) {
            return null;
        }

        $normalized = preg_replace('/\D/', '', $zipCode);

        return $normalized !== '' ? $normalized : null;
    }

    public static function getComputedLocation(): string
    {
        return 'location';
    }

    protected function addressableAddresses(): ?MorphMany
    {
        $addressable = $this->addressable;

        if (! is_object($addressable) || ! method_exists($addressable, 'addresses')) {
            return null;
        }

        $addresses = $addressable->addresses();

        return $addresses instanceof MorphMany ? $addresses : null;
    }
}
