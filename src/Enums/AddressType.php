<?php

namespace A21ns1g4ts\FilamentBrAddress\Enums;

use Filament\Support\Contracts\HasLabel;

enum AddressType: string implements HasLabel
{
    case Home = 'home';
    case Work = 'work';
    case Billing = 'billing';
    case Shipping = 'shipping';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Home => __('filament-br-address::filament-br-address.address_types.home'),
            self::Work => __('filament-br-address::filament-br-address.address_types.work'),
            self::Billing => __('filament-br-address::filament-br-address.address_types.billing'),
            self::Shipping => __('filament-br-address::filament-br-address.address_types.shipping'),
        };
    }
}
