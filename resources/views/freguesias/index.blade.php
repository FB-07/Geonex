@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.parish') => route('freguesias.index'),
    ]" />
@endsection
 
@section('content')

    <div class="box mt-6">
        <div class="flex flex-col gap-4">
            <x-items-per-page name="resultsPerPage" default="10" />
            <x-tecnical-table>
                <x-slot:head>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">ID</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">MUNICIPIOS_ID</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">NOME</th>
                </x-slot:head>

                @foreach($freguesias as $freguesia)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-3 py-4">{{ $freguesia->id }}</td>
                        <td class="px-3 py-4">{{ $freguesia->municipio->nome ?? 'N/A' }}</td>
                        <td class="px-3 py-4">{{ $freguesia->nome }}</td>

                        <td class="px-3 py-4">
                            <x-tecnical-tactions
                                :editData="route('freguesias.edit', $freguesia)"
                                :deleteData="route('freguesias.destroy', $freguesia)"
                            />
                        </tr>
                    </tr>
                @endforeach
            </x-tecnical-table>
        {{ $freguesias->links() }}
        </div class="overflow-x-auto">
    </div>
@endsection
 