@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.cp') => route('tec-cp'),
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
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">CP</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">FREGUESIA_ID</th>
                </x-slot:head>

                @foreach($cps as $cp)
                    <tr class="divide-y divide-gray-200">
                        <td class="px-3 py-4">{{ $cp->codigo }}</td>
                        <td class="px-3 py-4">{{ $cp->freguesia_id }}</td>

                        <x-tecnical-tactions
                            :editData="route('tec-cp', $cp)"
                            :deleteData="route('tec-cp', $cp)"
                        />
                    </tr>
                @endforeach
            </x-tecnical-table>
        {{ $cps->links() }}
        </div class="overflow-x-auto">
    </div>
@endsection
 