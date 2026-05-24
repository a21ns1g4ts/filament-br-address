<?php

namespace A21ns1g4ts\FilamentBrAddress;

use A21ns1g4ts\FilamentBrAddress\Filament\Forms\AddressForm;
use A21ns1g4ts\FilamentBrAddress\Filament\Tables\AddressTable;

class FilamentBrAddress
{
    /**
     * @return array<int, mixed>
     */
    public function form(
        bool $withMap = true,
        ?string $mapProvider = null,
        string | \Closure | null $mapAccessToken = null,
        string | \Closure | null $mapApiKey = null,
    ): array {
        return AddressForm::configure($withMap, $mapProvider, $mapAccessToken, $mapApiKey);
    }

    /**
     * @return array<int, mixed>
     */
    public function tableColumns(): array
    {
        return AddressTable::getDefaultColumns();
    }

    /**
     * @return array<int, mixed>
     */
    public function tableFilters(): array
    {
        return AddressTable::getDefaultFilters();
    }
}
