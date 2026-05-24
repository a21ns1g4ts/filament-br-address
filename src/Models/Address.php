<?php

namespace A21ns1g4ts\FilamentBrAddress\Models;

use A21ns1g4ts\FilamentBrAddress\Enums\AddressType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

            if ($address->is_default && $address->addressable) {
                $address->addressable->addresses()
                    ->whereKeyNot($address->getKey())
                    ->update(['is_default' => false]);
            }
        });

        static::saved(function (Address $address): void {
            if (! $address->addressable) {
                return;
            }

            $hasDefault = $address->addressable->addresses()
                ->where('is_default', true)
                ->exists();

            if (! $hasDefault) {
                $address->addressable->addresses()
                    ->oldest()
                    ->first()
                    ?->updateQuietly(['is_default' => true]);
            }
        });

        static::deleted(function (Address $address): void {
            if (! $address->addressable) {
                return;
            }

            if (! $address->addressable->addresses()->where('is_default', true)->exists()) {
                $address->addressable->addresses()
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
            get: fn (): array => [
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
        );
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn (): string => collect([
                $this->street,
                $this->number,
                $this->neighborhood,
                $this->city,
                $this->state,
                $this->zip_code,
                $this->country,
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
}
