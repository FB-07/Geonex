@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.queues') => route('tecnical-distritos'),
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
            <x-items-per-page name="resultsPerPage" default="10" />
            <x-tecnical-table>
                <x-slot:head>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">ID</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">NOME</th>
                </x-slot:head>

                @foreach($distritos as $distrito)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-3 py-4">{{ $distrito->id }}</td>
                        <td class="px-3 py-4">{{ $distrito->nome }}</td>

                        <x-tecnical-tactions
                            :editData="route('tecnical-distritos', $distrito)"
                            :deleteData="route('tecnical-distritos', $distrito)"
                        />
                    </tr>
                @endforeach
            </x-tecnical-table>
        {{ $distritos->links() }}
        </div class="overflow-x-auto">
    </div>
@endsection
 