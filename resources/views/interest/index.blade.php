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
                                        <span>
                                            @if ($g->image)
                                                <img class="w-7 h-7 ml-1.5 rounded-full" src="{{ Storage::url($g->image) }}" alt="">
                                            @elseif ($g->icon)
                                                <x-dynamic-component :component="'ionicon-' . $g->icon" class="ml-1.5 text-gray-500 w-6 h-6"/>
                                            @endif
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500 mt-0.5">
                                        {{ $g->distrito_nome ?? '-' }} /
                                        {{ $g->municipio_nome ?? '-' }} /
                                        {{ $g->freguesia_nome ?? '-' }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500">
                                        <x-tecnical-tactions-poi
                                            :editData="route('interest.edit', $key)" 
                                            :deleteData="route('interest.destroy', $key)" 
                                            :poi="$g"
                                        />
                                    </span>
                                </div>
                            </summary>

                            <form method="POST" action="{{ route('interest.place.detach', $g->id) }}" onsubmit="return confirm(@js(__('interest.confirmDetach')))">
                                @csrf
                                @method('DELETE')

                                <div class="box mt-2 px-3 pb-4">
                                    <table class="min-w-full divide-y divide-gray-300 table-fixed">
                                        <thead>
                                            <tr>
                                                <th class="px-3 py-3.5">
                                                    <input type="checkbox" id="check-all-{{ $g->id }}">
                                                </th>
                                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Local</th>
                                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Morada</th>
                                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Lat</th>
                                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Lng</th>
                                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tipos</th>
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-gray-200 text-gray-500">
                                            @forelse ($places as $p)
                                                <tr>
                                                    <td class="px-3 py-3.5 text-sm">
                                                        <input type="checkbox" 
                                                            name="places[]" 
                                                            value="{{ $p->place_id }}"
                                                            class="place-checkbox-{{ $g->id }}">
                                                    </td>

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
                                                                    <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700">
                                                                        {{ $t }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-3 py-3.5 text-sm text-gray-500 text-center">
                                                        <div class="flex flex-col items-center justify-center gap-2">
                                                            <img src="{{ asset('images/loader.gif') }}" alt="Carregando" class="w-12 h-12">
                                                            <span>Carregando, recarregue a página se necessário</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    <div class="mt-3">
                                        <button type="submit"
                                            class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 disabled:opacity-50"
                                            disabled>
                                            Remover selecionados
                                        </button>
                                    </div>
                                </div>
                            </form>
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

    document.querySelectorAll('[id^="check-all-"]').forEach(master => {
        master.addEventListener('change', function () {
            const groupId = this.id.replace('check-all-', '');

            document.querySelectorAll('.place-checkbox-' + groupId)
                .forEach(cb => cb.checked = this.checked);

            toggleButton(groupId);
        });
    });

    document.querySelectorAll('form').forEach(form => {
        const checkboxes = form.querySelectorAll('input[name="places[]"]');
        const button = form.querySelector('button[type="submit"]');

        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const checked = form.querySelectorAll('input[name="places[]"]:checked').length;
                button.disabled = checked === 0;
            });
        });
    });

    function toggleButton(groupId) {
        const form = document.querySelector(`#check-all-${groupId}`).closest('form');
        const button = form.querySelector('button[type="submit"]');
        const checked = form.querySelectorAll('input[name="places[]"]:checked').length;

        button.disabled = checked === 0;
    }

});
</script>
@endpush

@endsection