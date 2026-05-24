<?php

namespace A21ns1g4ts\FilamentBrAddress\Filament\Tables;

use A21ns1g4ts\FilamentBrAddress\Enums\AddressType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;

class AddressTable
{
    /**
     * @return array<int, mixed>
     */
    public static function getDefaultColumns(): array
    {
        return [
            TextColumn::make('street')
                ->label(__('filament-br-address::filament-br-address.fields.street'))
                ->searchable()
                ->sortable(),

            TextColumn::make('number')
                ->label(__('filament-br-address::filament-br-address.fields.number'))
                ->searchable(),

            TextColumn::make('neighborhood')
                ->label(__('filament-br-address::filament-br-address.fields.neighborhood'))
                ->searchable()
                ->sortable(),

            TextColumn::make('city')
                ->label(__('filament-br-address::filament-br-address.fields.city'))
                ->searchable()
                ->sortable(),

            TextColumn::make('state')
                ->label(__('filament-br-address::filament-br-address.fields.state'))
                ->sortable(),

            TextColumn::make('zip_code')
                ->label(__('filament-br-address::filament-br-address.fields.zip_code'))
                ->formatStateUsing(fn (?string $state): ?string => static::formatZipCode($state))
                ->searchable(),

            TextColumn::make('type')
                ->label(__('filament-br-address::filament-br-address.fields.type'))
                ->badge(),

            ToggleColumn::make('is_default')
                ->label(__('filament-br-address::filament-br-address.fields.is_default'))
                ->sortable()
                ->afterStateUpdated(function ($record, bool $state): void {
                    if (! $state || ! $record->addressable) {
                        return;
                    }

                    $record->addressable->addresses()
                        ->whereKeyNot($record->getKey())
                        ->update(['is_default' => false]);
                }),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function getDefaultFilters(): array
    {
        return [
            SelectFilter::make('type')
                ->label(__('filament-br-address::filament-br-address.fields.type'))
                ->options(AddressType::class),
        ];
    }

    protected static function formatZipCode(?string $state): ?string
    {
        $zipCode = preg_replace('/\D/', '', (string) $state);

        if (strlen($zipCode) !== 8) {
            return $state;
        }

        return substr($zipCode, 0, 5) . '-' . substr($zipCode, 5);
    }
}
