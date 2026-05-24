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
            showControls: config.showControls,
            loading: false,
            error: null,
            locationState: config.locationState || { lat: null, lng: null },
            loaded: false,
            map: null,
            marker: null,
            is3D: false,
            showTraffic: false,
            styleMenuOpen: false,
            currentStyle: 'satellite',
            lastGeocodedLocation: null,
            initialLocation: null,
            styles: {},
            init() {
                const bootWhenReady = () => {
                    if (window.filamentBrAddressMap) {
                        Object.assign(this, window.filamentBrAddressMap(config));
                        this.init();
                        return;
                    }

                    setTimeout(bootWhenReady, 100);
                };

                bootWhenReady();
            },
            geocodeAddress() {},
            toggle3D() {},
            toggleTraffic() {},
            setStyle() {},
            goToMarker() {},
            goToLastGeocoded() {},
            calculateFromAddress() {},
            resetToInitial() {},
            getCurrentLocation() {},
            zoomIn() {},
            zoomOut() {},
            formatCoordinate(value) {
                const number = Number(value || 0);

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
    class="relative w-full overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800"
    style="height: {{ $height }}px; min-height: {{ $height }}px;"
>
    <div x-ref="mapContainer" class="absolute inset-0 z-0 h-full w-full bg-gray-100 dark:bg-gray-900"></div>

    <div
        x-show="showControls"
        class="absolute bottom-4 right-4 z-40 flex items-center gap-2 rounded-xl border border-gray-200 bg-white/95 px-2 py-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900/95"
    >
        <div class="flex items-center rounded-lg bg-gray-100/50 p-0.5 dark:bg-gray-800/50">
            <button type="button" x-on:click="zoomIn()" x-tooltip="'Aproximar'" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 shadow-sm transition-all hover:bg-white dark:text-gray-300 dark:hover:bg-gray-700">
                <x-heroicon-o-plus class="h-4 w-4" />
            </button>
            <button type="button" x-on:click="zoomOut()" x-tooltip="'Afastar'" class="flex h-7 w-7 items-center justify-center rounded-md text-gray-600 shadow-sm transition-all hover:bg-white dark:text-gray-300 dark:hover:bg-gray-700">
                <x-heroicon-o-minus class="h-4 w-4" />
            </button>
        </div>

        <div class="mx-0.5 h-4 w-px bg-gray-200 dark:bg-gray-700"></div>

        <button
            type="button"
            x-on:click="toggle3D()"
            x-tooltip="'Alternar visão 3D'"
            class="flex h-8 w-8 items-center justify-center rounded-lg transition-colors"
            :class="is3D ? 'text-primary-600 bg-primary-50 dark:bg-primary-500/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
            <x-heroicon-o-cube class="h-5 w-5" />
        </button>

        <button
            type="button"
            x-on:click="toggleTraffic()"
            x-tooltip="'Mostrar trânsito'"
            class="flex h-8 w-8 items-center justify-center rounded-lg transition-colors"
            :class="showTraffic ? 'text-primary-600 bg-primary-50 dark:bg-primary-500/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
            <x-heroicon-o-truck class="h-5 w-5" />
        </button>

        <div class="relative" x-on:click.away="styleMenuOpen = false">
            <button type="button" x-on:click="styleMenuOpen = ! styleMenuOpen" x-tooltip="'Trocar estilo do mapa'" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                <x-heroicon-o-square-3-stack-3d class="h-5 w-5" />
            </button>
            <div
                x-show="styleMenuOpen"
                x-transition
                class="absolute bottom-12 right-0 z-[110] w-44 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800"
                style="display: none;"
            >
                <template x-for="(style, key) in styles" :key="key">
                    <button
                        type="button"
                        x-on:click="setStyle(key)"
                        class="flex w-full items-center px-4 py-2.5 text-left text-xs font-medium hover:bg-gray-50 dark:hover:bg-gray-700"
                        :class="currentStyle === key ? 'text-primary-600 bg-primary-50 dark:bg-primary-500/10' : 'text-gray-600 dark:text-gray-400'"
                    >
                        <span x-text="style.label"></span>
                    </button>
                </template>
            </div>
        </div>

        <div class="mx-0.5 h-4 w-px bg-gray-200 dark:bg-gray-700"></div>

        <button type="button" x-on:click="getCurrentLocation()" x-tooltip="'Minha localização'" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-emerald-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <x-heroicon-o-cursor-arrow-rays class="h-5 w-5" />
        </button>
        <button type="button" x-on:click="goToMarker()" x-tooltip="'Ir para marcador'" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <x-heroicon-o-viewfinder-circle class="h-5 w-5" />
        </button>
        <button type="button" x-on:click="goToLastGeocoded()" x-show="lastGeocodedLocation" x-tooltip="'Voltar para localização pesquisada'" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <x-heroicon-o-map-pin class="h-5 w-5" />
        </button>
        <button type="button" x-on:click="calculateFromAddress()" x-tooltip="'Recalcular do endereço'" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <x-heroicon-o-arrow-path class="h-5 w-5" />
        </button>
        <button type="button" x-on:click="resetToInitial()" x-show="initialLocation" x-tooltip="'Resetar para posição inicial'" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-600 transition-colors hover:bg-gray-100 hover:text-warning-600 dark:text-gray-300 dark:hover:bg-gray-700">
            <x-heroicon-o-arrow-uturn-left class="h-5 w-5" />
        </button>
    </div>

    <div
        x-show="loading || error"
        x-transition
        class="absolute inset-0 z-[100000] flex items-center justify-center rounded-2xl bg-white/80 transition-all duration-300 dark:bg-gray-900/80"
        style="display: none;"
    >
        <div class="flex flex-col items-center gap-3">
            <template x-if="loading">
                <div class="flex flex-col items-center gap-4">
                    <x-filament::loading-indicator class="h-10 w-10 text-primary-600" />
                    <span class="text-sm font-bold uppercase tracking-widest text-gray-900 dark:text-white">
                        {{ __('filament-br-address::filament-br-address.map.loading') }}
                    </span>
                </div>
            </template>

            <template x-if="error">
                <div class="flex flex-col items-center gap-2 rounded-2xl border border-danger-200 bg-white p-4 shadow-xl dark:bg-gray-800">
                    <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-danger-600" />
                    <span class="text-xs font-semibold text-danger-700 dark:text-danger-400" x-text="error"></span>
                    <button type="button" x-on:click="error = null; destroy(); boot()" class="mt-2 rounded-lg bg-danger-600 px-4 py-1.5 text-[10px] font-bold text-white transition-colors hover:bg-danger-500">
                        {{ __('filament-br-address::filament-br-address.map.restart') }}
                    </button>
                </div>
            </template>
        </div>
    </div>

    <div class="absolute bottom-4 left-4 z-40 rounded-xl border border-gray-200 bg-white/95 px-3 py-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900/95">
        <div class="flex items-center gap-3 text-[10px] font-mono uppercase tracking-tight text-gray-600 dark:text-gray-400">
            <div class="flex items-center gap-1">
                <span class="font-bold text-primary-600">LAT:</span>
                <span x-text="formatCoordinate(locationState?.lat)"></span>
            </div>
            <div class="flex items-center gap-1">
                <span class="font-bold text-primary-600">LNG:</span>
                <span x-text="formatCoordinate(locationState?.lng)"></span>
            </div>
        </div>
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
                    resizeObserver: null,
                    loading: false,
                    loaded: false,
                    error: null,
                    styleMenuOpen: false,
                    currentStyle: 'satellite',
                    is3D: false,
                    showTraffic: false,
                    projection: 'globe',
                    initialLocation: null,
                    lastGeocodedLocation: null,
                    lastGeocodedAddress: null,
                    pendingAddress: null,
                    pendingShowNotification: false,
                    defaultLocation: { lat: -15.7801, lng: -47.9292 },
                    rawConfig: config,
                    styles: {
                        streets: { label: 'Ruas', light: 'mapbox://styles/mapbox/streets-v12', dark: 'mapbox://styles/mapbox/dark-v11' },
                        outdoors: { label: 'Relevo', light: 'mapbox://styles/mapbox/outdoors-v12', dark: 'mapbox://styles/mapbox/outdoors-v12' },
                        light: { label: 'Minimalista (Claro)', light: 'mapbox://styles/mapbox/light-v11', dark: 'mapbox://styles/mapbox/light-v11' },
                        dark: { label: 'Minimalista (Escuro)', light: 'mapbox://styles/mapbox/dark-v11', dark: 'mapbox://styles/mapbox/dark-v11' },
                        navigation: { label: 'Navegação', light: 'mapbox://styles/mapbox/navigation-day-v1', dark: 'mapbox://styles/mapbox/navigation-night-v1' },
                        satellite: { label: 'Satélite', light: 'mapbox://styles/mapbox/satellite-streets-v12', dark: 'mapbox://styles/mapbox/satellite-streets-v12' },
                    },

                    init() {
                        const customProvider = window.filamentBrAddressMapProviders?.[this.provider];

                        if (customProvider) {
                            Object.assign(this, customProvider(this.rawConfig, this));
                        }

                        this.boot();
                    },

                    async boot() {
                        this.loading = false;
                        this.error = null;

                        try {
                            if (this.provider === 'google') {
                                await this.bootGoogle();
                                return;
                            }

                            if (this.provider !== 'mapbox' && window.filamentBrAddressMapProviders?.[this.provider]) {
                                return;
                            }

                            await this.bootMapbox();
                        } catch (error) {
                            console.error(error);
                            this.error = '{{ __('filament-br-address::filament-br-address.map.boot_error') }}';
                        }
                    },

                    destroy() {
                        if (this.resizeObserver) {
                            this.resizeObserver.disconnect();
                            this.resizeObserver = null;
                        }

                        if (this.map && typeof this.map.remove === 'function') {
                            try {
                                this.map.remove();
                            } catch (error) {
                                console.warn('Filament BR Address: error removing map', error);
                            }
                        }

                        this.map = null;
                        this.marker = null;
                        this.loaded = false;
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
                            style: this.getMapboxStyle(),
                            center: [center.lng, center.lat],
                            zoom: this.hasCoordinates() ? 18 : 12,
                            projection: { name: this.projection },
                            pitch: 0,
                            attributionControl: false,
                            antialias: true,
                        });

                        this.resizeObserver = new ResizeObserver(() => {
                            if (this.map) {
                                this.map.resize();
                            }
                        });
                        this.resizeObserver.observe(this.$refs.mapContainer);

                        this.map.on('load', () => {
                            this.loaded = true;
                            this.map.resize();

                            this.marker = new mapboxgl.Marker({ draggable: this.draggable, color: '#2563eb' })
                                .setLngLat([center.lng, center.lat])
                                .addTo(this.map);

                            if (this.pendingAddress && !this.initialLocation) {
                                this.geocodeAddress(this.pendingAddress, this.pendingShowNotification);
                                this.pendingAddress = null;
                            }

                            this.marker.on('dragend', () => {
                                const lngLat = this.marker.getLngLat();
                                this.updateCoordinates(lngLat.lat, lngLat.lng);
                            });
                        });

                        this.$watch('locationState', (newState) => {
                            if (!newState?.lat || !newState?.lng || !this.loaded || !this.marker || !this.map) {
                                return;
                            }

                            const lat = parseFloat(newState.lat);
                            const lng = parseFloat(newState.lng);

                            if (Number.isNaN(lat) || Number.isNaN(lng)) {
                                return;
                            }

                            const currentLngLat = this.marker.getLngLat();
                            const distance = Math.sqrt(Math.pow(currentLngLat.lat - lat, 2) + Math.pow(currentLngLat.lng - lng, 2));

                            if (distance > 0.00001) {
                                this.marker.setLngLat([lng, lat]);

                                if (!this.map.isMoving()) {
                                    this.map.flyTo({ center: [lng, lat], zoom: 18, essential: true });
                                }
                            }
                        });

                        this.map.on('click', (event) => {
                            if (!this.draggable) {
                                return;
                            }

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
                            tilt: 0,
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
                            if (!this.draggable) {
                                return;
                            }

                            this.updateCoordinates(event.latLng.lat(), event.latLng.lng());
                        });

                        this.loaded = true;
                    },

                    async geocodeAddress(address, showNotification = true) {
                        if (!address) {
                            return;
                        }

                        if (!this.loaded) {
                            this.pendingAddress = address;
                            this.pendingShowNotification = showNotification;
                            return;
                        }

                        if (this.lastGeocodedAddress === address) {
                            return;
                        }

                        if (showNotification) {
                            this.loading = true;
                        }

                        this.error = null;
                        this.lastGeocodedAddress = address;

                        try {
                            if (this.provider === 'google') {
                                await this.geocodeWithGoogle(address);
                            } else {
                                await this.geocodeWithMapbox(address, showNotification);
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

                    async geocodeWithMapbox(address, showNotification = true) {
                        if (!this.accessToken) {
                            return;
                        }

                        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${encodeURIComponent(this.accessToken)}&limit=1&country=br&types=address,postcode,place&language=pt`;
                        const response = await fetch(url);
                        const data = await response.json();

                        if (data.features?.length > 0) {
                            const [lng, lat] = data.features[0].center;

                            if (data.features[0].relevance < 0.5) {
                                console.warn('Filament BR Address: low Mapbox relevance', data.features[0].relevance);
                            }

                            this.flyTo(lat, lng);
                            this.updateCoordinates(lat, lng);
                            this.lastGeocodedLocation = { lat, lng };

                            return;
                        }

                        if (showNotification && typeof FilamentNotification !== 'undefined') {
                            new FilamentNotification()
                                .title('{{ __('filament-br-address::filament-br-address.map.address_not_found') }}')
                                .warning()
                                .send();
                        }
                    },

                    async geocodeWithGoogle(address) {
                        if (!window.google?.maps) {
                            return;
                        }

                        const geocoder = new google.maps.Geocoder();

                        await new Promise((resolve) => {
                            geocoder.geocode({ address, region: 'BR' }, (results, status) => {
                                if (status === 'OK' && results?.length) {
                                    const location = results[0].geometry.location;
                                    const lat = location.lat();
                                    const lng = location.lng();
                                    this.flyTo(lat, lng);
                                    this.updateCoordinates(lat, lng);
                                    this.lastGeocodedLocation = { lat, lng };
                                }

                                resolve();
                            });
                        });
                    },

                    calculateFromAddress() {
                        if (!this.$wire || typeof this.$wire.get !== 'function' || !this.statePath) {
                            return;
                        }

                        const parentPath = this.parentPath();
                        const street = this.$wire.get(parentPath + '.street');
                        const number = this.$wire.get(parentPath + '.number');
                        const zipCode = this.$wire.get(parentPath + '.zip_code');
                        const neighborhood = this.$wire.get(parentPath + '.neighborhood');
                        const city = this.$wire.get(parentPath + '.city');
                        const state = this.$wire.get(parentPath + '.state');
                        const country = this.$wire.get(parentPath + '.country') || 'Brasil';

                        let address = '';

                        if (street) {
                            address = [
                                street,
                                number ? `, ${number}` : '',
                                neighborhood ? ` - ${neighborhood}` : '',
                                city ? `, ${city}` : '',
                                state ? ` - ${state}` : '',
                                zipCode ? `, CEP ${zipCode}` : '',
                                `, ${country}`,
                            ].join('');
                        } else if (zipCode) {
                            address = `CEP ${zipCode}, ${country}`;
                        }

                        if (address) {
                            this.geocodeAddress(address, true);
                            return;
                        }

                        this.error = '{{ __('filament-br-address::filament-br-address.map.insufficient_address') }}';
                        setTimeout(() => this.error = null, 3000);
                    },

                    getCurrentLocation() {
                        if (!navigator.geolocation) {
                            return;
                        }

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

                        if (Number.isNaN(lat) || Number.isNaN(lng)) {
                            return;
                        }

                        this.locationState = { lat, lng };

                        if (this.provider === 'google' && this.marker) {
                            this.marker.setPosition({ lat, lng });
                        }

                        if (this.provider !== 'google' && this.marker) {
                            this.marker.setLngLat([lng, lat]);
                        }

                        if (this.$wire && typeof this.$wire.set === 'function' && this.statePath) {
                            const parentPath = this.parentPath();
                            this.$wire.set(parentPath + '.lat', lat, false);
                            this.$wire.set(parentPath + '.lng', lng, false);
                        }
                    },

                    toggle3D() {
                        this.is3D = !this.is3D;

                        if (this.provider === 'google' && this.map) {
                            this.map.setTilt(this.is3D ? 45 : 0);
                            return;
                        }

                        if (this.map?.easeTo) {
                            this.map.easeTo({ pitch: this.is3D ? 60 : 0 });
                        }
                    },

                    toggleTraffic() {
                        this.showTraffic = !this.showTraffic;

                        if (this.provider !== 'mapbox' || !this.map || !this.loaded) {
                            return;
                        }

                        if (!this.map.getSource('filament-br-address-mapbox-traffic')) {
                            this.map.addSource('filament-br-address-mapbox-traffic', {
                                type: 'vector',
                                url: 'mapbox://mapbox.mapbox-traffic-v1',
                            });
                        }

                        if (!this.map.getLayer('filament-br-address-mapbox-traffic')) {
                            this.map.addLayer({
                                id: 'filament-br-address-mapbox-traffic',
                                type: 'line',
                                source: 'filament-br-address-mapbox-traffic',
                                'source-layer': 'traffic',
                                paint: {
                                    'line-width': 2,
                                    'line-color': [
                                        'match',
                                        ['get', 'congestion'],
                                        'low', '#22c55e',
                                        'moderate', '#eab308',
                                        'heavy', '#f97316',
                                        'severe', '#ef4444',
                                        '#64748b',
                                    ],
                                },
                            });
                        }

                        this.map.setLayoutProperty(
                            'filament-br-address-mapbox-traffic',
                            'visibility',
                            this.showTraffic ? 'visible' : 'none',
                        );
                    },

                    setStyle(key) {
                        if (!this.styles[key]) {
                            return;
                        }

                        this.currentStyle = key;
                        this.styleMenuOpen = false;

                        if (this.provider === 'google') {
                            return;
                        }

                        if (this.map?.setStyle) {
                            this.map.setStyle(this.styles[key].light);
                            this.map.once('style.load', () => {
                                if (this.showTraffic) {
                                    this.showTraffic = false;
                                    this.toggleTraffic();
                                }
                            });
                        }
                    },

                    goToMarker() {
                        if (!this.marker || !this.map) {
                            return;
                        }

                        if (this.provider === 'google') {
                            const position = this.marker.getPosition();
                            this.map.setCenter(position);
                            this.map.setZoom(18);
                            return;
                        }

                        this.map.flyTo({ center: this.marker.getLngLat(), zoom: 18 });
                    },

                    goToLastGeocoded() {
                        if (!this.lastGeocodedLocation || !this.map) {
                            return;
                        }

                        this.updateCoordinates(this.lastGeocodedLocation.lat, this.lastGeocodedLocation.lng);
                        this.flyTo(this.lastGeocodedLocation.lat, this.lastGeocodedLocation.lng);
                    },

                    resetToInitial() {
                        if (!this.initialLocation || !this.map) {
                            return;
                        }

                        this.updateCoordinates(this.initialLocation.lat, this.initialLocation.lng);
                        this.flyTo(this.initialLocation.lat, this.initialLocation.lng);
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
                        if (!this.map) {
                            return;
                        }

                        this.provider === 'google' ? this.map.setZoom(this.map.getZoom() + 1) : this.map.zoomIn();
                    },

                    zoomOut() {
                        if (!this.map) {
                            return;
                        }

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
                            const location = {
                                lat: parseFloat(this.locationState.lat),
                                lng: parseFloat(this.locationState.lng),
                            };

                            this.initialLocation = location;

                            return location;
                        }

                        return this.defaultLocation;
                    },

                    getMapboxStyle() {
                        if (this.mapboxStyle) {
                            return this.mapboxStyle;
                        }

                        return this.styles[this.currentStyle].light;
                    },

                    formatCoordinate(value) {
                        const number = Number(value || 0);

                        return Number.isNaN(number) ? '0.000000' : number.toFixed(6);
                    },
                };
            };
        </script>
    @endpush
@endonce
