@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.p_interest') => route('interest.index'),
        __('index.edit') => '#',
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

    <div x-data class="h-full">
        <form action="{{ route('interest.update', $poi->nome) }}" method="POST" class="flex flex-col h-[calc(100vh-430px)]">
            @csrf
            @method('PUT')
            <div class="box flex-1 flex flex-col overflow-y-auto custom-scrollbar overflow-x-visible">
                <x-input
                    name="nome"
                    label="{{ __('levels.name') }}"
                    type="text"
                    value="{{ old('nome', $poi->nome) }}"
                    required
                />
                <x-input 
                    type="color" 
                    name="color" 
                    label="{{ __('levels.colour') }}"
                    id="color" 
                    value="{{ old('color', $poi->color ?? '#ff0000') }}" 
                />
            </div>
            <div class="mt-8 flex items-center justify-end gap-x-4">
                <x-button-link class="bg-gray-700" :href="route('interest.index')">{{ __('index.cancel') }}</x-button-link>
                <x-button type="submit">{{ __('index.save') }}</x-button>
            </div>
        </form>
    </div>
@endsection