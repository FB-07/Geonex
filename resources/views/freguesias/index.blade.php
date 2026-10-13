@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.parish') => route('freguesias.index'),
    ]" />
@endsection
 
@section('content')

    <div class="box mt-6">
        <div class="flex flex-col gap-4">
            <x-tecnical-filters :search="__('technical.search')" :selects="[
                ['name' => 'distrito_id', 'label' => __('technical.district'), 'options' => $distritos],
                ['name' => 'municipio_id', 'label' => __('technical.municipality'), 'options' => $municipios]
            ]" />
            <x-items-per-page name="resultsPerPage" default="10" />
            <x-tecnical-table>
                <x-slot:head>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.id') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.district') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.municipality') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.parish') }}</th>
                </x-slot:head>

                @forelse($freguesias as $freguesia)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-3 py-4">{{ $freguesia->id }}</td>
                        <td class="px-3 py-4">{{ $freguesia->municipio?->distrito?->nome ?? 'N/A' }}</td>
                        <td class="px-3 py-4">{{ $freguesia->municipio->nome ?? 'N/A' }}</td>
                        <td class="px-3 py-4">{{ $freguesia->nome }}</td>

                        <td class="px-3 py-4">
                            <x-tecnical-tactions
                                :editData="route('freguesias.edit', $freguesia)"
                                :deleteData="route('freguesias.destroy', $freguesia)"
                            />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">{{ __('technical.empty') }}</td></tr>
                @endforelse
            </x-tecnical-table>
        {{ $freguesias->links() }}
        </div>
    </div>
@endsection
 