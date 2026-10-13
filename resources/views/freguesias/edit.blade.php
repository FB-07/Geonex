@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.parish') => route('freguesias.index'),
        __('technical.edit') => '#',
    ]" />
@endsection

@section('content')
    <x-tecnical-edit :action="route('freguesias.update', $freguesia)" :back="route('freguesias.index')">
        <x-select label="{{ __('technical.municipality') }}" name="municipio_id"
            :options="$municipios->mapWithKeys(fn ($m) => [$m->id => $m->nome . ' (' . ($m->distrito->nome ?? '') . ')'])"
            :selected="$freguesia->municipio_id" placeholder="{{ __('technical.select') }}" />
        <x-input label="{{ __('technical.parish') }}" name="nome" value="{{ old('nome', $freguesia->nome) }}" />
    </x-tecnical-edit>
@endsection
