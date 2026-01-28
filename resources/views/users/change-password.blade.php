@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.users') => route('users.index'),
        __('navbar.editUser') => '#',
    ]" />
@endsection

@section('content')
    <form>
        <div class="flex gap-x-10">
            <div class="flex flex-1 flex-col gap-y-10">
                <div class="flex-1">
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900">
                        Password</label>
                    <div class="mt-2">
                        <input type="password" name="password"
                            class="block w-full rounded-md border-0 py-1.5 text-sm leading-6 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600">
                    </div>
                </div>

                <div class="flex-1">
                    <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">Confirmar
                        password</label>
                    <div class="mt-2">
                        <input type="password" name="password_confirmation"
                            class="block w-full rounded-md border-0 py-1.5 text-sm leading-6 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600">
                    </div>
                </div>
            </div>
            <div class="flex w-72 items-end justify-end gap-x-6">
                <x-button type="submit" class="ml-auto w-full">Atualizar password</x-button>
            </div>
        </div>
    </form>
@endsection
