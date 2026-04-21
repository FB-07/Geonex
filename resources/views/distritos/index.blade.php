@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.distri') => route('distritos.index'),
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
                </x-slot:head>

                @foreach($distritos as $distrito)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-3 py-4">{{ $distrito->id }}</td>
                        <td class="px-3 py-4">{{ $distrito->nome }}</td>
                        
                        <td class="px-3 py-4">
                            <x-tecnical-tactions
                                :editData="route('distritos.edit', $distrito)"
                                :deleteData="route('distritos.destroy', $distrito)"
                            />
                        </td>
                    </tr>
                @endforeach
            </x-tecnical-table>
        {{ $distritos->links() }}
        </div class="overflow-x-auto">
    </div>
@endsection
 