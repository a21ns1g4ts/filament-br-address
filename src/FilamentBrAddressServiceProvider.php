<?php

namespace A21ns1g4ts\FilamentBrAddress;

use A21ns1g4ts\FilamentBrAddress\Commands\FilamentBrAddressCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentBrAddressServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-br-address';

    public static string $viewNamespace = 'filament-br-address';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name(static::$name)
            ->hasCommand(FilamentBrAddressCommand::class);

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_addresses_table',
        ];
    }
}
