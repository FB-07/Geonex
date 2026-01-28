@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.dashboard') => route('map.index'),
    ]" />
@endsection

@section('content')
    <div id="content" class="flex flex-row pb-2 h-5px">
        <div class="rounded-full bg-proximo-700 backdrop-blur border border-gray-200 px-3 py-2.5 text-white shadow-md focus:ring-indigo-500">
            <button id="btn-filtros" type="button">Filtros</button>
        </div>

        <div id="filtros-dropdown" class="hidden rounded-full pr-1">
            <div class="flex items-center justify-center block w-full min-h-10 mt-1 ml-1 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6">
                <input type="checkbox" value="zona" class="rounded-full" id="filtro-zona">
                <label class="ml-1 mr-2" for="filtro-zona">Zona</label>

                <input type="checkbox" value="filtro2" class="rounded-full" id="filtro-2">
                <label class="ml-1 mr-2" for="filtro-2">2</label>

                <input type="checkbox" value="filtro3" class="rounded-full" id="filtro-3">
                <label class="ml-1 mr-2" for="filtro-3">3</label>
            </div>
        </div>

        <div id="distrito" class="hidden pr-1">
            <select id="distritos-select" class="block w-full min-h-9 mt-1 ml-1 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6">
                <option value="" disabled selected>Selecione uma das opções</option>
            </select>
        </div>

        <div id="municipios" class="hidden pr-1">
            <select id="municipios-select" disabled class="block w-full min-h-9 mt-1 ml-1 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6">
                <option value="" disabled selected>Selecione uma das opções</option>
            </select>
        </div>

        <div id="freguesias" class="hidden pr-1">
            <select id="freguesias-select" disabled class="block w-full min-h-9 mt-1 ml-1 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6">
                <option value="" disabled selected>Selecione uma das opções</option>
            </select>
        </div>

        <div id="nofiltro" class="hidden flex items-center justify-center mt-1.5 mb-1 pl-1 cursor-pointer">
            <a id="btn-limpar" class="flex items-center bg-red-600 hover:bg-red-500 backdrop-blur border shadow-md rounded-full py-1.5 px-2.5 gap-1 text-gray-100 hover:text-gray-50">
                <x-heroicon-c-x-circle class="h-5 w-5" />
                <span class="text-sm pb-0.5">Limpar</span>
            </a>
        </div>
    </div>

    <div id="iconsMap" class="w-10 h-auto absolute z-[1001] top-[150px] right-[43px] flex flex-col items-center justify-center bg-white shadow-md">
        <a id="btn-locPais"><x-heroicon-m-arrows-pointing-out class="w-6 h-10 cursor-pointer"/></a>
        <a id="btn-localizacao"><x-heroicon-m-arrows-pointing-in class="w-6 h-10 cursor-pointer"/></a>
    </div>

    <div class="h-10 w-8 absolute z-[1001] bg-white text-gray-700 top-[150px] left-[355px] cursor-pointer">
        <input id="search" type="search" class="relative border shadow-md bg-white border-gray-400 hover:bg-gray-100" placeholder="Pesquisar(ex: Continente)">
    </div>

    <div class="h-8 w-8 flex items-center justify-center rounded-full bg-white absolute z-[1001] top-[200px] left-[355px]">
        <a id="recent"><x-heroicon-o-exclamation-circle class="w-6 h-6 cursor-pointer"/></a>
    </div>

    <div id="recentInfo" class="hidden absolute p-2 rounded-xm z-[1001] top-[235px] left-[355px] bg-white max-h-[108px] w-[205px] overflow-y-scroll">
        <p class="flex justify-center bg-gray-500 rounded-md text-white font-bold">Recentes</p>
        <div id="recentList"></div>
    </div>

    <div>
        <div id="map" style="height: 77.5vh; width: 75.5vw;"></div>
    </div>

    <script>
        window.APP = {
            googleMapsKey: @json(config('services.google_maps.key')),
            apiBase: @json(url('/api')),
        };
    </script>

    @push('scripts')
        @vite('resources/js/pages/map.js')
    @endpush
@endsection
