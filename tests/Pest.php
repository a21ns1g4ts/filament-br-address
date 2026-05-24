<?php

use A21ns1g4ts\FilamentBrAddress\Forms\Components\AddressMap;
use A21ns1g4ts\FilamentBrAddress\Tests\TestCase;
use Filament\Forms\Components\ViewField;

uses(TestCase::class)->in(__DIR__);

function hasAddressMap(array $components): bool
{
    return findAddressMap($components) instanceof AddressMap;
}

function findAddressMap(array $components): ?AddressMap
{
    foreach ($components as $component) {
        if ($component instanceof AddressMap) {
            return $component;
        }

        $childMap = findAddressMap(getRawChildComponents($component));

        if ($childMap instanceof AddressMap) {
            return $childMap;
        }
    }

    return null;
}

function hasLoadingOverlay(array $components): bool
{
    foreach ($components as $component) {
        if ($component instanceof ViewField && $component->getName() === 'loading_overlay') {
            return true;
        }

        if (hasLoadingOverlay(getRawChildComponents($component))) {
            return true;
        }
    }

    return false;
}

function getRawChildComponents(mixed $component): array
{
    $class = new ReflectionClass($component);

    while ($class) {
        if ($class->hasProperty('childComponents')) {
            $property = $class->getProperty('childComponents');
            $property->setAccessible(true);

            return collect($property->getValue($component))
                ->flatMap(fn ($components) => is_array($components) ? $components : [$components])
                ->all();
        }

        $class = $class->getParentClass();
    }

    return [];
}
