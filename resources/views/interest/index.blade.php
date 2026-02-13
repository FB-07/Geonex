@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.places') => route('interest'),
    ]" />
@endsection

@section('content')

    <div class="mb-4">
        <x-button-link class="bg-gray-700" :href="route('interest-add')">
            Adicionar
        </x-button-link>
    </div>

    <div class="box mt-2 px-3 pb-4">
        <table class="min-w-full divide-y divide-gray-300 table-fixed">
            <thead>
                <tr>
                    <th class="px-3 py-3.5 text-left text-base font-semibold text-gray-900">
                        Nome
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 text-gray-500">

                @forelse($groups as $nome => $places)
                    <tr>
                        <td class="px-0 py-0">
                            <details class="group">
                                <summary
                                    class="list-none cursor-pointer px-3 py-3.5 text-sm flex items-center justify-between">
                                    <span class="font-medium text-gray-900">
                                        {{ $nome }}
                                    </span>

                                    <span class="text-gray-500 group-open:hidden">
                                        <x-tecnical-tactions
                                            :editData="route('tec-freguesias', $freguesia)"
                                            :deleteData="route('tec-freguesias', $freguesia)"
                                        />
                                    </span>
                                    
                                </summary>

                                <div class="box mt-2 px-3 pb-4">
                                    <table class="min-w-full divide-y divide-gray-300 table-fixed">
                                        <thead>
                                            <tr>
                                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    Local
                                                </th>
                                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    Morada
                                                </th>
                                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    Lat
                                                </th>
                                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    Lng
                                                </th>
                                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                                    Tipos
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-gray-200 text-gray-500">
                                            @foreach ($places as $p)
                                                <tr>
                                                    <td class="px-3 py-3.5 text-sm text-gray-900 font-medium">
                                                        {{ $p->name }}
                                                        <div class="text-xs text-gray-400">
                                                            {{ $p->place_id }}
                                                        </div>
                                                    </td>

                                                    <td class="px-3 py-3.5 text-sm">
                                                        {{ $p->formatted_address ?? '-' }}
                                                    </td>

                                                    <td class="px-3 py-3.5 text-sm">
                                                        {{ $p->lat ?? '-' }}
                                                    </td>

                                                    <td class="px-3 py-3.5 text-sm">
                                                        {{ $p->lng ?? '-' }}
                                                    </td>

                                                    <td class="px-3 py-3.5 text-sm">
                                                        @php
                                                            $types = $p->types;

                                                            if (is_string($types)) {
                                                                $decoded = json_decode($types, true);
                                                                $types =
                                                                    json_last_error() === JSON_ERROR_NONE
                                                                        ? $decoded
                                                                        : [$types];
                                                            }

                                                            $types = is_array($types) ? $types : [];
                                                        @endphp

                                                        @if (count($types))
                                                            <div class="flex flex-wrap gap-1">
                                                                @foreach (array_slice($types, 0, 5) as $t)
                                                                    <span
                                                                        class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700">
                                                                        {{ $t }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-3.5 text-sm text-gray-500">
                            Sem pontos de interesse.
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>

        <div class="mt-4">
            {{ $namesPage->links() }}
        </div>

    </div>

@endsection
