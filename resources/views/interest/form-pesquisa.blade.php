@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.zones') => route('interest'),
        isset($zone) ? __('zones.edit') : __('zones.newZone') => '#',
    ]" />
@endsection

@section('content')
    <x-errors />

    <div x-data class="h-full">

        <form action="{{ route('interest-controller') }}" method="POST" id="zone-form" class="flex flex-col h-[calc(100vh-150px)]">
            @csrf

            <div class="box flex-1 flex flex-col overflow-y-auto gap-4 custom-scrollbar overflow-x-visible">

                <x-input
                    name="pesquisa"
                    label="Pesquisa"
                    type="text"
                    required
                />

                <x-select label="Distrito" name="distrito" :options="[]" />
                <x-select label="Município" name="municipio" :options="[]" />
                <x-select label="Freguesia" name="freguesia" :options="[]" />

            </div>

            {{-- Footer --}}
            <div class="mt-8 flex items-center justify-end gap-x-4">
                <x-button-link class="bg-gray-700" :href="route('interest')">{{ __('index.cancel') }}</x-button-link>
                <x-button type="submit">{{ __('index.save') }}</x-button>
            </div>
        </form>
    </div>

    @push('scripts')
        @vite('resources/js/pages/points_interest.js')
    @endpush
@endsection
