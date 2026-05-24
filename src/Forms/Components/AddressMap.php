<?php

namespace A21ns1g4ts\FilamentBrAddress\Forms\Components;

use Closure;
use Filament\Forms\Components\ViewField;

class AddressMap extends ViewField
{
    protected string | Closure | null $provider = null;

    protected string | Closure | null $accessToken = null;

    protected string | Closure | null $apiKey = null;

    protected int | Closure | null $height = null;

    protected bool | Closure $draggable = true;

    protected bool | Closure $showControls = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->view('filament-br-address::components.address-map')
            ->dehydrated(false);
    }

    public function provider(string | Closure | null $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function accessToken(string | Closure | null $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function apiKey(string | Closure | null $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function height(int | Closure | null $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function draggable(bool | Closure $draggable = true): static
    {
        $this->draggable = $draggable;

        return $this;
    }

    public function showControls(bool | Closure $showControls = true): static
    {
        $this->showControls = $showControls;

        return $this;
    }

    public function getProvider(): string
    {
        return $this->evaluate($this->provider) ?? (string) config('filament-br-address.maps.default', 'mapbox');
    }

    public function getAccessToken(): ?string
    {
        return $this->evaluate($this->accessToken) ?? config('filament-br-address.maps.mapbox.access_token');
    }

    public function getApiKey(): ?string
    {
        return $this->evaluate($this->apiKey) ?? config('filament-br-address.maps.google.api_key');
    }

    public function getHeight(): int
    {
        return $this->evaluate($this->height) ?? (int) config('filament-br-address.maps.height', 400);
    }

    public function isDraggable(): bool
    {
        return (bool) $this->evaluate($this->draggable);
    }

    public function shouldShowControls(): bool
    {
        return (bool) $this->evaluate($this->showControls);
    }
}
