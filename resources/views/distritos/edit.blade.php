@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.distri') => route('distritos.index'),
        __('technical.edit') => '#',
    ]" />
@endsection

@section('content')
    <x-tecnical-edit :action="route('distritos.update', $distrito)" :back="route('distritos.index')">
        <x-input label="{{ __('technical.district') }}" name="nome" value="{{ old('nome', $distrito->nome) }}" />
    </x-tecnical-edit>
@endsection
