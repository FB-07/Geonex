@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.cp') => route('codigos-postais.index'),
    ]" />
@endsection
 
@section('content')

    <div class="box mt-6">
        <div class="flex flex-col gap-4">
            <x-items-per-page name="resultsPerPage" default="10" />
            <x-tecnical-table>
                <x-slot:head>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">CP</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">FREGUESIA_ID</th>
                </x-slot:head>

                @foreach($cps as $cp)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-3 py-4">{{ $cp->codigo }}</td>
                        <td class="px-3 py-4">{{ $cp->freguesia_id }}</td>
                            
                        <td class="px-3 py-4">
                            <x-tecnical-tactions
                                :editData="route('codigos-postais.edit', $cp)"
                                :deleteData="route('codigos-postais.destroy', $cp)"
                            />
                        </td>
                    </tr>
                @endforeach
            </x-tecnical-table>
        {{ $cps->links() }}
        </div class="overflow-x-auto">
    </div>
@endsection
 