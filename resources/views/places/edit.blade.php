@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.places') => route('places.index'),
        __('technical.edit') => '#',
    ]" />
@endsection

@section('content')
    <x-tecnical-edit :action="route('places.update', $place)" :back="route('places.index')">
        <x-input label="{{ __('technical.name') }}" name="name" value="{{ old('name', $place->name) }}" />
        <x-input label="{{ __('technical.address') }}" name="formatted_address" value="{{ old('formatted_address', $place->formatted_address) }}" />
        <x-input label="{{ __('technical.lat') }}" name="lat" value="{{ old('lat', $place->lat) }}" />
        <x-input label="{{ __('technical.lng') }}" name="lng" value="{{ old('lng', $place->lng) }}" />
    </x-tecnical-edit>
@endsection
