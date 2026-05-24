@php
    $provider = $getProvider();
    $accessToken = $getAccessToken();
    $apiKey = $getApiKey();
    $height = $getHeight();
    $isDraggable = $isDraggable();
    $showControls = $shouldShowControls();
    $statePath = $getStatePath();
    $targetPath = $statePath ?: (string) \Illuminate\Support\Str::uuid();
    $initialState = $getState() ?: ['lat' => null, 'lng' => null];
@endphp

<div
    wire:ignore
    x-data="(window.filamentBrAddressMap || function (config) {
        return {
            target: config.target,
            showControls: false,
            loading: true,
            error: null,
            locationState: config.locationState || { lat: null, lng: null },
            init() {
                const bootWhenReady = () => {
                    if (!window.filamentBrAddressMap) {
                        setTimeout(bootWhenReady, 50);
                        return;
                    }

                    Object.assign(this, window.filamentBrAddressMap(config));
                    this.init();
                };

                bootWhenReady();
            },
            geocodeAddress() {},
            zoomIn() {},
            zoomOut() {},
            geolocate() {},
            calculateFromAddress() {},
            formatCoordinate(value) {
                const number = parseFloat(value || 0);

                return Number.isNaN(number) ? '0.000000' : number.toFixed(6);
            },
        };
    })({
        provider: @js($provider),
        accessToken: @js($accessToken),
        apiKey: @js($apiKey),
        mapboxStyle: @js(config('filament-br-address.maps.mapbox.style')),
        googleMapId: @js(config('filament-br-address.maps.google.map_id')),
        locationState: @js($initialState),
        statePath: @js($statePath),
        target: @js($targetPath),
        draggable: @js($isDraggable),
        showControls: @js($showControls),
    })"
    x-on:filament-br-address-geocode-address.window="if (!$event.detail.target || $event.detail.target === target) geocodeAddress($event.detail.address, $event.detail.showNotification ?? true)"
    class="relative w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800"
    style="height: {{ $height }}px; min-height: {{ $height }}px;"
>
    <div x-ref="mapContainer" class="absolute inset-0 z-0 h-full w-full bg-gray-100 dark:bg-gray-900"></div>

    <div
        x-show="loading || error"
        x-transition
        class="absolute inset-0 z-20 flex items-center justify-center bg-white/80 dark:bg-gray-900/80"
        style="display: none;"
    >
        <div class="flex max-w-sm flex-col items-center gap-3 rounded-lg bg-white px-4 py-3 text-center shadow dark:bg-gray-900">
            <template x-if="loading">
                <div class="flex flex-col items-center gap-3">
                    <x-filament::loading-indicator class="h-8 w-8 text-primary-600" />
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">
                        {{ __('filament-br-address::filament-br-address.map.loading') }}
                    </span>
                </div>
            </template>

            <template x-if="error">
                <span class="text-sm font-medium text-danger-600" x-text="error"></span>
            </template>
        </div>
    </div>

    <div
        x-show="showControls"
        class="absolute bottom-3 right-3 z-10 flex items-center gap-1 rounded-lg border border-gray-200 bg-white/95 p-1 shadow dark:border-gray-800 dark:bg-gray-900/95"
    >
        <button type="button" x-on:click="zoomIn()" class="flex h-8 w-8 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
            <x-heroicon-o-plus class="h-4 w-4" />
        </button>
        <button type="button" x-on:click="zoomOut()" class="flex h-8 w-8 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
            <x-heroicon-o-minus class="h-4 w-4" />
        </button>
        <button type="button" x-on:click="geolocate()" class="flex h-8 w-8 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
            <x-heroicon-o-cursor-arrow-rays class="h-4 w-4" />
        </button>
        <button type="button" x-on:click="calculateFromAddress()" class="flex h-8 w-8 items-center justify-center rounded-md text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
            <x-heroicon-o-arrow-path class="h-4 w-4" />
        </button>
    </div>

    <div class="absolute bottom-3 left-3 z-10 rounded-lg border border-gray-200 bg-white/95 px-2 py-1 text-[10px] font-mono text-gray-600 shadow dark:border-gray-800 dark:bg-gray-900/95 dark:text-gray-300">
        <span>LAT: </span><span x-text="formatCoordinate(locationState?.lat)"></span>
        <span class="ml-2">LNG: </span><span x-text="formatCoordinate(locationState?.lng)"></span>
    </div>
</div>

@once
    @push('styles')
        <link id="filament-br-address-mapbox-css" rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css">
    @endpush

    @push('scripts')
        <script>
            window.filamentBrAddressMapProviders = window.filamentBrAddressMapProviders || {};

            window.filamentBrAddressLoadScript = window.filamentBrAddressLoadScript || function (src, id) {
                return new Promise((resolve, reject) => {
                    if (id && document.getElementById(id)) {
                        resolve();
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = src;
                    script.async = true;
                    if (id) script.id = id;
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            };

            window.filamentBrAddressLoadStyle = window.filamentBrAddressLoadStyle || function (href, id) {
                if (id && document.getElementById(id)) {
                    return;
                }

                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                if (id) link.id = id;
                document.head.appendChild(link);
            };

            window.filamentBrAddressMap = window.filamentBrAddressMap || function (config) {
                return {
                    provider: config.provider || 'mapbox',
                    accessToken: config.accessToken,
                    apiKey: config.apiKey,
                    mapboxStyle: config.mapboxStyle,
                    googleMapId: config.googleMapId,
                    locationState: config.locationState || { lat: null, lng: null },
                    statePath: config.statePath,
                    target: config.target,
                    draggable: config.draggable,
                    showControls: config.showControls,
                    map: null,
                    marker: null,
                    loading: false,
                    error: null,
                    loaded: false,
                    defaultLocation: { lat: -15.7801, lng: -47.9292 },
                    rawConfig: config,

                    init() {
                        const customProvider = window.filamentBrAddressMapProviders?.[this.provider];

                        if (customProvider) {
                            Object.assign(this, customProvider(this.rawConfig, this));
                        }

                        this.boot();
                    },

                    async boot() {
                        this.loading = true;
                        this.error = null;

                        try {
                            if (this.provider === 'google') {
                                await this.bootGoogle();
                            } else {
                                await this.bootMapbox();
                            }
                        } catch (error) {
                            console.error(error);
                            this.error = '{{ __('filament-br-address::filament-br-address.map.boot_error') }}';
                        } finally {
                            this.loading = false;
                        }
                    },

                    async bootMapbox() {
                        if (!this.accessToken) {
                            this.error = '{{ __('filament-br-address::filament-br-address.map.missing_mapbox_token') }}';
                            return;
                        }

                        window.filamentBrAddressLoadStyle('https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css', 'filament-br-address-mapbox-css');

                        if (!window.mapboxgl) {
                            await window.filamentBrAddressLoadScript('https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js', 'filament-br-address-mapbox');
                        }

                        mapboxgl.accessToken = this.accessToken;

                        const center = this.getInitialLocation();
                        this.map = new mapboxgl.Map({
                            container: this.$refs.mapContainer,
                            style: this.mapboxStyle || 'mapbox://styles/mapbox/satellite-streets-v12',
                            center: [center.lng, center.lat],
                            zoom: this.hasCoordinates() ? 18 : 12,
                            attributionControl: false,
                        });

                        this.map.on('load', () => {
                            this.loaded = true;
                            this.marker = new mapboxgl.Marker({ draggable: this.draggable, color: '#2563eb' })
                                .setLngLat([center.lng, center.lat])
                                .addTo(this.map);

                            this.marker.on('dragend', () => {
                                const lngLat = this.marker.getLngLat();
                                this.updateCoordinates(lngLat.lat, lngLat.lng);
                            });
                        });

                        this.map.on('click', (event) => {
                            if (!this.draggable) return;
                            this.updateCoordinates(event.lngLat.lat, event.lngLat.lng);
                        });
                    },

                    async bootGoogle() {
                        if (!this.apiKey) {
                            this.error = '{{ __('filament-br-address::filament-br-address.map.missing_google_key') }}';
                            return;
                        }

                        if (!window.google?.maps) {
                            await window.filamentBrAddressLoadScript(`https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(this.apiKey)}&libraries=places`, 'filament-br-address-google-maps');
                        }

                        const center = this.getInitialLocation();
                        this.map = new google.maps.Map(this.$refs.mapContainer, {
                            center,
                            zoom: this.hasCoordinates() ? 18 : 12,
                            mapId: this.googleMapId || undefined,
                            streetViewControl: false,
                            mapTypeControl: false,
                        });

                        this.marker = new google.maps.Marker({
                            position: center,
                            map: this.map,
                            draggable: this.draggable,
                        });

                        this.marker.addListener('dragend', (event) => {
                            this.updateCoordinates(event.latLng.lat(), event.latLng.lng());
                        });

                        this.map.addListener('click', (event) => {
                            if (!this.draggable) return;
                            this.updateCoordinates(event.latLng.lat(), event.latLng.lng());
                        });

                        this.loaded = true;
                    },

                    async geocodeAddress(address, showNotification = true) {
                        if (!address) return;

                        this.loading = showNotification;
                        this.error = null;

                        try {
                            if (this.provider === 'google') {
                                await this.geocodeWithGoogle(address);
                            } else {
                                await this.geocodeWithMapbox(address);
                            }
                        } catch (error) {
                            console.error(error);
                            if (showNotification) {
                                this.error = '{{ __('filament-br-address::filament-br-address.map.geocode_error') }}';
                            }
                        } finally {
                            this.loading = false;
                        }
                    },

                    async geocodeWithMapbox(address) {
                        if (!this.accessToken) return;

                        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${encodeURIComponent(this.accessToken)}&limit=1&country=br&types=address,postcode,place&language=pt`;
                        const response = await fetch(url);
                        const data = await response.json();

                        if (!data.features?.length) return;

                        const [lng, lat] = data.features[0].center;
                        this.flyTo(lat, lng);
                        this.updateCoordinates(lat, lng);
                    },

                    async geocodeWithGoogle(address) {
                        if (!window.google?.maps) return;

                        const geocoder = new google.maps.Geocoder();

                        await new Promise((resolve) => {
                            geocoder.geocode({ address, region: 'BR' }, (results, status) => {
                                if (status === 'OK' && results?.length) {
                                    const location = results[0].geometry.location;
                                    const lat = location.lat();
                                    const lng = location.lng();
                                    this.flyTo(lat, lng);
                                    this.updateCoordinates(lat, lng);
                                }

                                resolve();
                            });
                        });
                    },

                    calculateFromAddress() {
                        if (!this.$wire || !this.statePath) return;

                        const parentPath = this.parentPath();
                        const parts = [
                            this.$wire.get(parentPath + '.street'),
                            this.$wire.get(parentPath + '.number'),
                            this.$wire.get(parentPath + '.neighborhood'),
                            this.$wire.get(parentPath + '.city'),
                            this.$wire.get(parentPath + '.state'),
                            this.$wire.get(parentPath + '.zip_code') ? `CEP ${this.$wire.get(parentPath + '.zip_code')}` : null,
                            this.$wire.get(parentPath + '.country') || 'Brasil',
                        ].filter(Boolean);

                        this.geocodeAddress(parts.join(', '), true);
                    },

                    geolocate() {
                        if (!navigator.geolocation) return;

                        this.loading = true;
                        navigator.geolocation.getCurrentPosition((position) => {
                            this.updateCoordinates(position.coords.latitude, position.coords.longitude);
                            this.flyTo(position.coords.latitude, position.coords.longitude);
                            this.loading = false;
                        }, () => {
                            this.loading = false;
                        });
                    },

                    updateCoordinates(lat, lng) {
                        lat = parseFloat(lat);
                        lng = parseFloat(lng);

                        if (Number.isNaN(lat) || Number.isNaN(lng)) return;

                        this.locationState = { lat, lng };

                        if (this.provider === 'google' && this.marker) {
                            this.marker.setPosition({ lat, lng });
                        }

                        if (this.provider !== 'google' && this.marker) {
                            this.marker.setLngLat([lng, lat]);
                        }

                        if (this.$wire && this.statePath) {
                            const parentPath = this.parentPath();
                            this.$wire.set(parentPath + '.lat', lat, false);
                            this.$wire.set(parentPath + '.lng', lng, false);
                        }
                    },

                    flyTo(lat, lng) {
                        if (this.provider === 'google' && this.map) {
                            this.map.setCenter({ lat, lng });
                            this.map.setZoom(18);
                            return;
                        }

                        if (this.map?.flyTo) {
                            this.map.flyTo({ center: [lng, lat], zoom: 18, essential: true });
                        }
                    },

                    zoomIn() {
                        if (!this.map) return;
                        this.provider === 'google' ? this.map.setZoom(this.map.getZoom() + 1) : this.map.zoomIn();
                    },

                    zoomOut() {
                        if (!this.map) return;
                        this.provider === 'google' ? this.map.setZoom(this.map.getZoom() - 1) : this.map.zoomOut();
                    },

                    parentPath() {
                        const lastDotIndex = this.statePath.lastIndexOf('.');

                        return lastDotIndex !== -1 ? this.statePath.substring(0, lastDotIndex) : 'data';
                    },

                    hasCoordinates() {
                        return Boolean(this.locationState?.lat && this.locationState?.lng);
                    },

                    getInitialLocation() {
                        if (this.$wire && this.statePath) {
                            const parentPath = this.parentPath();
                            const lat = this.$wire.get(parentPath + '.lat');
                            const lng = this.$wire.get(parentPath + '.lng');

                            if (lat && lng) {
                                this.locationState = { lat, lng };
                            }
                        }

                        if (this.hasCoordinates()) {
                            return {
                                lat: parseFloat(this.locationState.lat),
                                lng: parseFloat(this.locationState.lng),
                            };
                        }

                        return this.defaultLocation;
                    },

                    formatCoordinate(value) {
                        const number = parseFloat(value || 0);

                        return Number.isNaN(number) ? '0.000000' : number.toFixed(6);
                    },
                };
            };
        </script>
    @endpush
@endonce
