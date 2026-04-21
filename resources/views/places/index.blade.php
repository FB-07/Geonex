@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.places') => route('places.index'),
    ]" />
@endsection
 
@section('content')

    <div class="box mt-6">
        <div class="flex flex-col gap-4">
            <x-items-per-page name="resultsPerPage" default="10" />
            <x-tecnical-table>
                <x-slot:head>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">ID</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">NOME</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">MORADA</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">DISTRITO</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">CONSELHO</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">FREGUESIA</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">LAT</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">LNG</th>
                </x-slot:head>

                @foreach ($places as $place)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-3 py-4">{{ $place->id }}</td>
                        <td class="px-3 py-4">{{ $place->name }}</td>
                        <td class="px-3 py-4">{{ $place->formatted_address }}</td>
                        <td class="px-3 py-4">POR FAZER</td>
                        <td class="px-3 py-4">POR FAZER</td>
                        <td class="px-3 py-4">POR FAZER</td>
                        <td class="px-3 py-4">{{ $place->lat }}</td>
                        <td class="px-3 py-4">{{ $place->lng }}</td>

                        <td class="px-3 py-4">
                            <x-tecnical-tactions
                                :editData="route('places.edit', $place)" 
                                :deleteData="route('places.destroy', $place)" 
                            />
                        </td>
                    </tr>
                @endforeach
            </x-tecnical-table>
            {{ $places->links() }}
        </div class="overflow-x-auto">
    </div>
@endsection
 
{{--4 tabelas distrios(distrito_id PK)<-municipios(municipio_id PK)<-freguesias(freguesia_id PK)<-codigos_postais(codigo PK) e quero pegar o codigo postal da morada do place e ver atraves das FK se os places estao no distrito/municipio/freguesia determidado pelo utilizador se tiver guarda na bd e mostra--}}