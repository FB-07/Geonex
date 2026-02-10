@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.queues') => route('tecnical-municipios'),
    ]" />
@endsection
 
@section('content')
    <!--
    <div class="mb-6 flex items-center gap-2">
        <div class="flex flex-col gap-y-2 w-full">
            filtros se quiseres
        </div>
        <div class="flex gap-3 mt-auto ml-auto ">
            <x-button-link class="" href="a">
                {{ __('queues.newQueue') }}
            </x-button-link>
        </div>
    </div>
    -->

    <div class="box mt-6">
        <div class="flex flex-col gap-4">
            <x-tecnical-table>
                <x-slot:head>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">ID</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">DISTRITO_ID</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">NOME</th>
                </x-slot:head>

                @foreach($municipios as $municipio)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-3 py-4">{{ $municipio->id }}</td>
                        <td class="px-3 py-4">{{ $municipio->distrito_id }}</td>
                        <td class="px-3 py-4">{{ $municipio->nome }}</td>

                        <x-tecnical-tactions
                            :editData="route('tecnical-municipios', $municipio)"
                            :deleteData="route('tecnical-municipios', $municipio)"
                        />
                    </tr>
                @endforeach
            </x-tecnical-table>
        </div>
    </div>
@endsection
 