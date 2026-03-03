@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.places') => route('interest.index'),
    ]" />
@endsection

@section('content')

<div class="mb-4">
    <x-button-link class="bg-gray-700" :href="route('interest.create')">
        Criar
    </x-button-link>
</div>

<div class="box mt-2 px-3 pb-4">
    <table class="min-w-full divide-y divide-gray-300 table-fixed">
        <thead>
            <tr>
                <th class="px-3 py-3.5 text-left text-base font-semibold text-gray-900">Nome</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 text-gray-500">
            @forelse($groupsPage as $g)
                @php
                    $key = $g->nome;
                    $places = $placesByGroup[$key] ?? collect();
                @endphp
                <tr>
                    <td class="px-0 py-0">
                        <details class="group">
                            <summary class="list-none cursor-pointer px-3 py-3.5 text-sm flex items-center justify-between">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-1">
                                        <span class="details-icon text-gray-500">
                                            <x-heroicon-o-chevron-right class="w-5 h-5"/>
                                        </span>
                                        <span class="font-medium text-gray-900">{{ $g->nome }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500 mt-0.5">
                                        {{ $g->distrito_nome ?? '-' }} /
                                        {{ $g->municipio_nome ?? '-' }} /
                                        {{ $g->freguesia_nome ?? '-' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500">
                                        <x-tecnical-tactions 
                                            :editData="route('interest.edit', $key)" 
                                            :deleteData="route('interest.destroy', $key)" 
                                        />
                                    </span>
                                </div>
                            </summary>

                            <div class="box mt-2 px-3 pb-4">
                                <table class="min-w-full divide-y divide-gray-300 table-fixed">
                                    <thead>
                                        <tr>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Local</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Morada</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Lat</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Lng</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tipos</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"></th>

                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 text-gray-500">
                                        @forelse ($places as $p)
                                            <tr>
                                                <td class="px-3 py-3.5 text-sm">{{ $p->name }}</td>
                                                <td class="px-3 py-3.5 text-sm">{{ $p->formatted_address ?? '-' }}</td>
                                                <td class="px-3 py-3.5 text-sm">{{ $p->lat ?? '-' }}</td>
                                                <td class="px-3 py-3.5 text-sm">{{ $p->lng ?? '-' }}</td>
                                                <td class="px-3 py-3.5 text-sm">
                                                    @php
                                                        $types = $p->types;
                                                        if (is_string($types)) {
                                                            $decoded = json_decode($types, true);
                                                            $types = json_last_error() === JSON_ERROR_NONE ? $decoded : [$types];
                                                        }
                                                        $types = is_array($types) ? $types : [];
                                                    @endphp
                                                    @if (count($types))
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach (array_slice($types, 0, 5) as $t)
                                                                <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700">{{ $t }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-3 py-3.5 text-sm">
                                                    <form method="POST" 
                                                        action="{{ route('interest.place.detach', [$g->id, $p->place_id]) }}"
                                                        onsubmit="return confirm('Remover este local do ponto de interesse?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="flex items-center gap-1 text-red-500 hover:bg-red-50 hover:underline hover:text-red-600">
                                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                                            <spam>Remover</spam>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-3 py-3.5 text-sm text-gray-500">Sem locais associados.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-3 py-3.5 text-sm text-gray-500">Sem pontos de interesse.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $groupsPage->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const detailsList = document.querySelectorAll('details');

        detailsList.forEach(details => {
            const iconContainer = details.querySelector('.details-icon');

            const closedIcon = `<x-heroicon-o-chevron-right class="w-5 h-5"/>`;
            const openIcon   = `<x-heroicon-o-chevron-down class="w-5 h-5"/>`;

            const updateIcon = () => {
                iconContainer.innerHTML = details.open ? openIcon : closedIcon;
            };

            updateIcon();

            details.addEventListener('toggle', () => {
                if (details.open) {
                    detailsList.forEach(d => {
                        if (d !== details && d.open) {
                            d.open = false;
                            const otherIcon = d.querySelector('.details-icon');
                            if (otherIcon) otherIcon.innerHTML = closedIcon;
                        }
                    });
                }
                updateIcon();
            });
        });
    });
</script>
@endpush

@endsection