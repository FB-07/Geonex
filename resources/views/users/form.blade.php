@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.users') => route('users.index'),
        isset($user) ? __('users.editUser') : __('users.create') => '#',
    ]" />
@endsection

@section('content')
    <form action="{{ isset($user) ? route('users.update', $user->gfuser_id) : route('users.store') }}" method="POST">

        @isset($user)
            @method('PUT')
        @endisset

        @csrf

        <div class="box flex flex-col">
            <div class="flex mb-6 gap-3">
                <input type="hidden" name="active" value="0">
                <div class="flex h-6 items-center">
                    <input id="active" name="active" type="checkbox" value="1"
                        @checked(old('active', isset($user) ? (in_array($user->gfuser_active, ['1', 'Y']) ? '1' : '0') : '1') === '1')
                        class="h-4 w-4 rounded border-gray-300 text-proximo-600 focus:ring-proximo-600">
                </div>
                <div class="text-sm leading-6">
                    <x-label for="active">{{ __('users.active') }}</x-label>
                </div>
            </div>

            <x-input label="{{ __('users.username') }}" name="username" :req="true"
                value="{{ old('username', $user->gfuser_alias ?? '') }}" />
            <x-input label="{{ __('users.name') }}" name="fullname" :req="true"
                value="{{ old('fullname', $user->gfuser_fullname ?? '') }}" />
            <x-input label="{{ __('users.email') }}" name="email" type="email" :req="true"
                value="{{ old('email', $user->gfuser_email ?? '') }}" />

            <x-input label="{{ __('users.password') }}" name="password" type="password" :req="! isset($user)" />
            <x-input label="{{ __('users.passwordConfirmation') }}" name="password_confirmation" type="password"
                :req="! isset($user)" />
            @isset($user)
                <p class="-mt-4 mb-6 text-xs text-gray-500">{{ __('users.passwordKeep') }}</p>
            @endisset

            <x-radio label="{{ __('users.gender') }}" name="sex" :options="[
                'M' => __('users.male'),
                'F' => __('users.female'),
            ]" :selected="old('sex', $user->gfuser_sex ?? null)" />

            <div class="mt-8 flex items-center justify-end gap-x-4">
                <x-button-link type="button" class="bg-gray-700 hover:bg-gray-800"
                    :href="route('users.index')">{{ __('users.cancel') }}</x-button-link>
                <x-button type="submit">{{ __('users.save') }}</x-button>
            </div>
        </div>
    </form>
@endsection
