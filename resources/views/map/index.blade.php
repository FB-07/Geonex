@extends('layouts/auth') 

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[__('navbar.dashboard') => route('map.index')]" />
@endsection 

@section('content')
    <div id="content" class="flex flex-row pb-2">
        <div class="rounded-full bg-proximo-700 backdrop-blur border border-gray-200 px-3 py-2.5 text-white shadow-md focus:ring-indigo-500">
            <button id="btn-filtros" type="button">Filtrar</button>
         </div>

        <div id="filtros-dropdown" class="hidden rounded-full pr-1">
            <div class="flex items-center justify-center block w-full min-h-10 mt-1 ml-1 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6">
                <input type="checkbox" value="zona" class="rounded-full" id="filtro-zona"> <label class="ml-1 mr-2" for="filtro-zona">Zona</label> 
                <input type="checkbox" value="tipo" class="rounded-full" id="filtro-tipo"> <label class="ml-1 mr-2" for="filtro-tipo">Tipo</label> 
            </div>
        </div>

        <div id="distrito" class="hidden pr-1"> 
            <select id="distritos-select" class="block w-full min-h-9 mt-1 ml-1 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6">
                <option value="" disabled selected>Selecione uma das opções</option>
            </select> 
        </div>

        <div id="municipios" class="hidden pr-1"> 
            <select id="municipios-select" class="block w-full min-h-9 mt-1 ml-1 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6">
                <option value="" disabled selected>Selecione uma das opções</option>
            </select> 
        </div>

        <div id="freguesias" class="hidden pr-1"> 
            <select id="freguesias-select" class="block w-full min-h-9 mt-1 ml-1 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6">
                <option value="" disabled selected>Selecione uma das opções</option>
            </select> 
        </div>

        <div id="categoria" class="hidden pr-1">
            <select id="category" class="block w-full min-h-9 mt-1 ml-1 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6">
                <option value="" disabled selected>Selecione uma das opções</option>

                <option value="automotive">Automotivo</option>
                <option value="business">Negócios</option>
                <option value="culture">Cultura</option>
                <option value="education">Educação</option>
                <option value="entertainment">Lazer</option>
                <option value="finance">Finanças</option>
                <option value="fooddrink">Alimentos e bebidas</option>
                <option value="geographic">Áreas geográficas</option>
                <option value="government">Governo</option>
                <option value="health">Saúde e bem-estar</option>
                <option value="housing">Imóveis</option>
                <option value="lodging">Hospedagem</option>
                <option value="services">Serviços</option>
                <option value="shopping">Compras</option>
                <option value="sports">Esportes</option>
                <option value="transportation">Transporte</option>
            </select>
        </div>

        <div id="nofiltro" class="hidden flex items-center justify-center mt-1.5 mb-1 pl-1 cursor-pointer"> 
            <a id="btn-limpar" class="flex items-center bg-red-600 hover:bg-red-500 backdrop-blur border shadow-md rounded-full py-1.5 px-2.5 gap-1 text-gray-100 hover:text-gray-50">
                <x-heroicon-c-x-circle class="h-5 w-5" /> 
                <span class="text-sm pb-0.5">Limpar</span> 
            </a> 
        </div>
    </div>

    <div id="mapWrapper" class="relative w-full h-full">
        <div id="iconsMap" class="w-10 h-auto absolute z-[1001] top-12 right-2.5 flex flex-col items-center justify-center bg-white shadow-md">
            <a id="btn-locPais"><x-heroicon-o-map class="text-gray-600 hover:text-black w-6 h-10 cursor-pointer" /></a> 
            <a id="btn-localizacao"><x-heroicon-o-map-pin class="text-gray-600 hover:text-black w-6 h-10 cursor-pointer" /></a> 
        </div>

        <div class="h-10 w-6 absolute z-[1001] text-gray-700 top-[20px] left-4">
            <input id="search" type="search"
                class="border shadow-md bg-white border-gray-400"
                placeholder="Pesquisar (ex: Continente)">
        </div>

        <div class="h-8 w-8 flex items-center justify-center rounded-full bg-white absolute z-[1001] top-[70px] left-4">
            <a id="pontos_interesse"><x-heroicon-o-bookmark class="text-gray-600 hover:text-black w-6 h-6 cursor-pointer" /></a>
        </div>

        <div id="pontos_interesseInfo" class="hidden absolute p-2 rounded-md z-[1001] top-[110px] left-4 bg-white max-h-[170px] w-[205px] overflow-y-scroll shadow-md">
            <p class="flex justify-center bg-gray-500 rounded-md text-white font-bold">Pontos de interesse</p>
            <div id="pontos_interesseList"></div>
        </div>

        <div class="h-8 w-8 flex items-center justify-center rounded-full bg-white absolute z-[1001] top-[70px] left-14">
            <a id="recent"><x-heroicon-o-clock class="text-gray-600 hover:text-black w-6 h-6 cursor-pointer" /></a> 
        </div>

        <div id="recentInfo"
            class="hidden absolute p-2 rounded-md z-[1001] top-[110px] left-4 bg-white max-h-[108px] w-[205px] overflow-y-scroll shadow-md">
            <p class="flex justify-center bg-gray-500 rounded-md text-white font-bold">Recentes</p>
            <div id="recentList"></div>
        </div>

        <span id="noRecent" class="hidden"> 
            <x-heroicon-o-x-circle class="w-6 h-6 text-red-500 hover:text-red-700 cursor-pointer" /> 
        </span>

        <div>
            <div id="map" style="height: 77.5vh; width: full;"></div>
        </div>
    </div>

    <script>
        window.APP = {
            googleMapsKey: @json(config('services.google_maps.key')),
            apiBase: @json(url('/api')),
        };
        /* 
            Filtros (Zona / Tipo)
            Markers -> 15 cores diferente
            Editar / Apagar
            User
            Password
        */
    </script> 
    
    @push('scripts')
        @vite('resources/js/pages/map.js')
    @endpush
@endsection
