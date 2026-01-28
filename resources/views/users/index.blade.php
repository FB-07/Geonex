@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.users') => route('users.index'),
    ]" />
@endsection

@section('search')
    <x-search-bar />
@endsection

@section('content')
    <div class="box">
        <div class="mb-6 flex items-center gap-2">
            <x-items-per-page name="resultsPerPage" default="10" />
            <x-button-link href="{{ route('users.create') }}" class="w-36 ml-auto">
                {{ __('users.newUser') }}
            </x-button-link>
        </div>

        <table class="min-w-full divide-y divide-gray-300">
            <thead>
                <tr>
                    <th scope="col"
                        class="hidden py-3.5 pl-0 pr-3 text-left text-sm font-semibold text-gray-900 sm:table-cell">
                        {{ __('users.username') }}
                    </th>
                    <th scope="col"
                        class="max-w-48 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:max-w-none">
                        {{ __('users.name') }}
                    </th>
                    <th scope="col"
                        class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 xl:table-cell">
                        {{ __('users.email') }}
                    </th>
                    <th scope="col"
                        class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 xl:table-cell">
                        {{ __('users.type') }}
                    </th>

                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($users as $user)
                    <tr>
                        <td class="hidden whitespace-nowrap py-4 pl-0 pr-3 text-sm font-medium text-gray-900 sm:table-cell">
                            {{ $user->gfuser_alias }}</td>
                        <td
                            class="max-w-48 overflow-hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 sm:max-w-none">
                            {{ $user->gfuser_fullname }}
                        </td>
                        <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 xl:table-cell">
                            {{ $user->gfuser_email }}
                        </td>
                        <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 xl:table-cell">
                            {{ $user->gfuser_type }}
                        </td>
                        <td class="ml-3 flex justify-end gap-2 whitespace-nowrap py-4 pr-0 text-sm font-medium">
                            <a href="{{ route('users.edit', $user->gfuser_id) }}"
                                class="text-proximo-600 hover:text-proximo-900">
                                {{ __('users.edit') }}</a>
                            <div>|</div>
                            <form action="{{ route('users.destroy', $user->gfuser_id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-proximo-600 hover:text-proximo-900">
                                    {{ __('users.delete') }}
                                </button>
                            </form>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
