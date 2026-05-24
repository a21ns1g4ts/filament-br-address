<?php

namespace A21ns1g4ts\FilamentBrAddress\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \A21ns1g4ts\FilamentBrAddress\FilamentBrAddress
 */
class FilamentBrAddress extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \A21ns1g4ts\FilamentBrAddress\FilamentBrAddress::class;
    }
}
