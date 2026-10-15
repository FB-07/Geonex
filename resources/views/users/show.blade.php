@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.operador') => '#',
    ]" />
@endsection

@section('content')
    <div class="box">
        <dl class="divide-y divide-gray-100">
            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6 text-gray-900">{{ __('users.username') }}</dt>
                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $user->gfuser_alias }}</dd>
            </div>
        </dl>
    </div>
    <div class="box">
        <dl class="divide-y divide-gray-100">

            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6 text-gray-900">{{ __('users.name') }}</dt>
                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $user->gfuser_fullname }}</dd>
            </div>
            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6 text-gray-900">{{ __('users.email') }}</dt>
                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $user->gfuser_email }}</dd>
            </div>
            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6 text-gray-900">{{ __('users.gender') }}</dt>
                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                    {{ $user->gfuser_sex === 'F' ? __('users.female') : __('users.male') }}</dd>
            </div>

        </dl>
    </div>

    <div class="mt-8 flex items-center justify-end gap-x-4">
        <x-button-link type="button" class="bg-gray-500" :href="route('profile.edit')">{{ __('users.edit') }}</x-button-link>
    </div>
@endsection
