@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[__('navbar.dashboard') => route('map.index')]" />
@endsection

@section('content')
    <div class="-m-6 h-[calc(100%+3rem)] overflow-hidden">
        <div id="mapWrapper" class="relative w-full h-full">
            <div id="iconsMap"
                class="w-10 h-auto absolute z-[1001] top-12 right-2.5 flex flex-col items-center justify-center bg-white shadow-md">
                <a id="btn-locPais"><x-heroicon-o-map class="text-gray-600 hover:text-black w-6 h-10 cursor-pointer" /></a>
                <a id="btn-localizacao"><x-heroicon-o-map-pin class="text-gray-600 hover:text-black w-6 h-10 cursor-pointer" /></a>
            </div>

            <div class="h-10 w-6 absolute z-[1001] text-gray-700 top-[20px] left-4">
                <input id="search" type="search" class="border shadow-md bg-white border-gray-400"
                    placeholder="{{ __('map.search') }}">
            </div>

            <div class="h-8 w-8 flex items-center justify-center rounded-full bg-white absolute z-[1001] top-[70px] left-4">
                <a id="pontos_interesse"><x-heroicon-o-map-pin class="text-gray-600 hover:text-black w-6 h-6 cursor-pointer" /></a>
            </div>

            <div id="pontos_interesseInfo"
                class="hidden absolute p-2 rounded-md z-[1001] top-[110px] left-4 bg-white max-h-[170px] w-[205px] overflow-y-scroll shadow-md">
                <p class="flex justify-center bg-gray-500 rounded-md text-white font-bold">{{ __('navbar.p_interest') }}</p>
                <div id="pontos_interesseList"></div>
            </div>

            <div
                class="h-8 w-8 flex items-center justify-center rounded-full bg-white absolute z-[1001] top-[70px] left-14">
                <a id="recent"><x-heroicon-o-clock class="text-gray-600 hover:text-black w-6 h-6 cursor-pointer" /></a>
            </div>

            <div id="recentInfo"
                class="hidden absolute p-2 rounded-md z-[1001] top-[110px] left-4 bg-white max-h-[108px] w-[205px] overflow-y-scroll shadow-md">
                <p class="flex justify-center bg-gray-500 rounded-md text-white font-bold">{{ __('map.recent') }}</p>
                <div id="recentList"></div>
            </div>

            <span id="noRecent" class="hidden">
                <x-heroicon-o-x-circle class="w-6 h-6 text-red-500 hover:text-red-700 cursor-pointer" />
            </span>

            <div id="map" class="h-full w-full"></div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/Pages/map.js')

        <script>
            window.APP = {
                googleMapsKey: @json(config('services.google_maps.key')),
                apiBase: @json(url('/api')),
                i18n: @json(__('map.geo')),
            };
        </script>
    @endpush
@endsection
