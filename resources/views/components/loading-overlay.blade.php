@php
    $targetPath = isset($getStatePath)
        ? str_replace('loading_overlay', 'location', $getStatePath())
        : (string) \Illuminate\Support\Str::uuid();
@endphp

<div
    x-data="{
        show: false,
        target: @js($targetPath),
    }"
    x-on:filament-br-address-start-zip-code-loading.window="if ($event.detail.target === target) { show = true; setTimeout(() => { show = false }, 10000) }"
    x-on:filament-br-address-stop-zip-code-loading.window="if ($event.detail.target === target) show = false"
    x-show="show"
    x-cloak
    class="absolute inset-0 z-20 flex items-center justify-center rounded-lg bg-white/80 transition-all duration-300 dark:bg-gray-900/80"
>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="flex flex-col items-center gap-4 rounded-lg border border-gray-200 bg-white/90 p-8 shadow-2xl dark:border-gray-800 dark:bg-gray-900/90">
        <x-filament::loading-indicator class="h-12 w-12 text-primary-600" />

        <div class="flex flex-col items-center gap-1">
            <span class="text-sm font-bold uppercase tracking-widest text-gray-900 dark:text-white">
                {{ __('filament-br-address::filament-br-address.loading_overlay.title') }}
            </span>
            <span class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {{ __('filament-br-address::filament-br-address.loading_overlay.description') }}
            </span>
        </div>
    </div>
</div>
