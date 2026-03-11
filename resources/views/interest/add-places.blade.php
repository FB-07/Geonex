@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.p_interest') => route('interest.index')
    ]" />
@endsection

@section('content')

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-100 p-4 text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="h-full">

        <form action="{{ route('interest.addPlaces', $poi->nome) }}" method="POST"
            class="flex flex-col h-[calc(100vh-550px)]">

            @csrf

            <div class="box flex-1 flex flex-col gap-4">

                <x-input
                    name="pesquisa"
                    label="Pesquisa"
                    type="text"
                    placeholder="Ex: restaurantes, cafés, museus..."
                    required
                />

            </div>

            <div class="mt-8 flex items-center justify-end gap-x-4">
                <x-button-link class="bg-gray-700" :href="route('interest.index')">{{ __('index.cancel') }}</x-button-link>
                <x-button type="submit">{{ __('index.add') }}</x-button>
            </div>

        </form>

    </div>
@endsection