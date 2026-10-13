@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.cp') => route('codigos-postais.index'),
        __('technical.edit') => '#',
    ]" />
@endsection

@section('content')
    <x-tecnical-edit :action="route('codigos-postais.update', $codigo_postal)" :back="route('codigos-postais.index')">
        <x-input label="{{ __('technical.code') }}" name="codigo" value="{{ old('codigo', $codigo_postal->codigo) }}" />
        <x-select label="{{ __('technical.parish') }}" name="freguesia_id"
            :options="$freguesias->mapWithKeys(fn ($f) => [$f->id => $f->nome . ' — ' . ($f->municipio->nome ?? '') . ' (' . ($f->municipio->distrito->nome ?? '') . ')'])"
            :selected="$codigo_postal->freguesia_id" placeholder="{{ __('technical.select') }}" />
    </x-tecnical-edit>
@endsection
