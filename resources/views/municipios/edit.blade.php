@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.munici') => route('municipios.index'),
        __('technical.edit') => '#',
    ]" />
@endsection

@section('content')
    <x-tecnical-edit :action="route('municipios.update', $municipio)" :back="route('municipios.index')">
        <x-select label="{{ __('technical.district') }}" name="distrito_id" :options="$distritos->pluck('nome', 'id')"
            :selected="$municipio->distrito_id" placeholder="{{ __('technical.select') }}" />
        <x-input label="{{ __('technical.municipality') }}" name="nome" value="{{ old('nome', $municipio->nome) }}" />
    </x-tecnical-edit>
@endsection
