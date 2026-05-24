<?php

namespace A21ns1g4ts\FilamentBrAddress\Forms\Components;

use Filament\Forms\Components\ViewField;

class LoadingOverlay extends ViewField
{
    protected string $view = 'filament-br-address::components.loading-overlay';

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->dehydrated(false)
            ->hiddenLabel()
            ->columnSpanFull();
    }
}
