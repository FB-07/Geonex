@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.cp') => route('codigos-postais.index'),
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
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('navbar.cp') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.district') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.municipality') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">{{ __('technical.parish') }}</th>
                </x-slot:head>

                @forelse($cps as $cp)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-3 py-4">{{ $cp->codigo }}</td>
                        <td class="px-3 py-4">{{ $cp->freguesia?->municipio?->distrito?->nome ?? '—' }}</td>
                        <td class="px-3 py-4">{{ $cp->freguesia?->municipio?->nome ?? '—' }}</td>
                        <td class="px-3 py-4">{{ $cp->freguesia?->nome ?? '—' }}</td>
                            
                        <td class="px-3 py-4">
                            <x-tecnical-tactions
                                :editData="route('codigos-postais.edit', $cp)"
                                :deleteData="route('codigos-postais.destroy', $cp)"
                            />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">{{ __('technical.empty') }}</td></tr>
                @endforelse
            </x-tecnical-table>
        {{ $cps->links() }}
        </div>
    </div>
@endsection
 