@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.places') => route('places.index'),
    ]" />
@endsection
 
@section('content')

    <div class="box mt-6">
        <div class="flex flex-col gap-4">
            <x-tecnical-filters :search="__('technical.search')" :selects="[
                ['name' => 'distrito_id', 'label' => __('technical.district'), 'options' => $distritos],
                ['name' => 'municipio_id', 'label' => __('technical.municipality'), 'options' => $municipios],
                ['name' => 'freguesia_id', 'label' => __('technical.parish'), 'options' => $freguesias]
            ]" />
            <x-items-per-page name="resultsPerPage" default="10" />
            <x-tecnical-table>
                <x-slot:head>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.id') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.name') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.address') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.district') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.municipality') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.parish') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.lat') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.lng') }}</th>
                </x-slot:head>

                @forelse ($places as $place)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-3 py-4">{{ $place->id }}</td>
                        <td class="px-3 py-4">{{ $place->name }}</td>
                        <td class="px-3 py-4">{{ $place->formatted_address }}</td>
                        <td class="px-3 py-4">{{ $place->distrito->nome ?? 'N/A' }}</td>
                        <td class="px-3 py-4">{{ $place->municipio->nome ?? 'N/A' }}</td>
                        <td class="px-3 py-4">{{ $place->freguesia->nome ?? 'N/A' }}</td>
                        <td class="px-3 py-4">{{ $place->lat }}</td>
                        <td class="px-3 py-4">{{ $place->lng }}</td>

                        <td class="px-3 py-4">
                            <x-tecnical-tactions
                                :editData="route('places.edit', $place)" 
                                :deleteData="route('places.destroy', $place)" 
                            />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-3 py-4 text-center text-sm text-gray-500">{{ __('technical.empty') }}</td></tr>
                @endforelse
            </x-tecnical-table>
            {{ $places->links() }}
        </div>
    </div>
@endsection
 
