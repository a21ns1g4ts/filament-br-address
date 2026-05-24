<?php

namespace A21ns1g4ts\FilamentBrAddress\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CepService
{
    /**
     * @return array<string, mixed>|null
     */
    public function get(string $zipCode): ?array
    {
        $zipCode = (string) preg_replace('/\D/', '', $zipCode);

        if (strlen($zipCode) !== 8) {
            return null;
        }

        $ttl = (int) config('filament-br-address.cep.cache_ttl', 86400);
        $cacheKey = "filament-br-address:cep:{$zipCode}";

        return Cache::remember($cacheKey, $ttl, fn () => $this->fetch($zipCode));
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function fetch(string $zipCode): ?array
    {
        $url = Str::of((string) config('filament-br-address.cep.base_url', 'https://brasilapi.com.br/api'))
            ->rtrim('/')
            ->append("/cep/v1/{$zipCode}")
            ->toString();

        $response = Http::acceptJson()
            ->timeout((int) config('filament-br-address.cep.timeout', 10))
            ->get($url);

        if (! $response->successful()) {
            return null;
        }

        /** @var array<string, mixed> $data */
        $data = $response->json();

        return $data;
    }
}
