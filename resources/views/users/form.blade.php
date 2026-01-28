@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.users') => route('users.index'), //TODO: Update translations
        isset($user) ? __('users.editUser') : __('users.create') => '#',
    ]" />
@endsection

@section('content')
    <form action="{{ isset($user) ? route('users.update', $user) : route('users.create') }}" method="POST">

        @isset($user)
            @method('PUT')
        @endisset

        @csrf

        <div class="box flex flex-col">
            <div class="flex mb-6 gap-3">
                <input type="hidden" name="active" value="N">
                <div class="flex h-6 items-center">
                    <input name="active" type="checkbox" value="Y"
                        {{ isset($user) && $user->gfuser_active === 'Y' ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-proximo-600 focus:ring-proximo-600">
                </div>
                <div class="text-sm leading-6">
                    <x-label>{{ __('users.active') }}</x-label>
                </div>
            </div>

            <x-input label="{{ __('users.username') }}" name="username"
                value="{{ $user->gfuser_alias ?? old('username') }}" />

            <x-input label="{{ __('users.name') }}" name="fullname"
                value="{{ $user->gfuser_fullname ?? old('fullname') }}" />
            <x-input label="{{ __('users.email') }}" name="email" type="email"
                value="{{ $user->gfuser_email ?? old('email') }}" />

            <x-radio label="{{ __('users.gender') }}" name="sex" :options="[
                'M' => __('users.male'),
                'F' => __('users.female'),
            ]" :selected="isset($user) ? $user->gfuser_sex : null" />

            <x-select label="{{ __('users.type') }}" name="type" :options="[
                'Doutor' => __('users.doctor'),
                'Técnico' => __('users.technician'),
                'Colheitas' => __('users.collections'),
                'Outros' => __('users.other'),
            ]" :selected="isset($user) ? $user->gfuser_type : null" />

            <x-input label="{{ __('users.title') }}" name="salut" value="{{ $user->gfuser_salut ?? old('salut') }}" />
            <x-input label="{{ __('users.speciality') }}" name="speciality"
                value="{{ $user->gfuser_speciality ?? old('speciality') }}" />


            <x-input label="{{ __('users.location') }}" name="place"
                value="{{ $user->gfuser_place ?? old('place') }}" />
            <x-input label="{{ __('users.clientID') }}" name="clientid"
                value="{{ $user->gfuser_clientid ?? old('clientid') }}" />
            <x-radio name="doctortype" :options="[
                '1' => __('users.withHelpers'),
                '0' => __('users.withoutHelpers'),
            ]" :selected="isset($user) ? $user->gfuser_doctortype : null" />

            <div class="mt-8 flex items-center justify-end gap-x-4">
                <x-button-link type="button" class="bg-gray-700 hover:bg-gray-800"
                    :href="route('users.index')">{{ __('users.cancel') }}</x-button-link>
                <x-button type="submit" class="">{{ __('users.save') }}</x-button>
            </div>
        </div>

    </form>
@endsection
