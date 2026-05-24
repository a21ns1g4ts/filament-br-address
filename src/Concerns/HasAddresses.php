<?php

namespace A21ns1g4ts\FilamentBrAddress\Concerns;

use A21ns1g4ts\FilamentBrAddress\Models\Address;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAddresses
{
    public function addresses(): MorphMany
    {
        return $this->morphMany($this->getAddressModelClass(), 'addressable');
    }

    public function defaultAddress(): ?Address
    {
        $address = $this->addresses()
            ->where('is_default', true)
            ->first();

        return $address instanceof Address ? $address : null;
    }

    /**
     * @return class-string<Address>
     */
    protected function getAddressModelClass(): string
    {
        return config('filament-br-address.address_model', Address::class);
    }
}
