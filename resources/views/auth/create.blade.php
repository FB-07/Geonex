@extends('layouts.app')

@section('body')

    <div class="flex min-h-full">
        <div class="flex flex-1 flex-col justify-center bg-proximo-600 px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <div>
                    <h2 class="mt-8 text-2xl font-bold leading-9 tracking-tight text-white">
                        {{ __('auth.signInToYourAccount') }}
                    </h2>
                </div>

                <div class="mt-10 flex flex-col gap-4">

                    <form action="{{ route('login') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="username"
                                class="block text-sm font-medium leading-6 text-white">{{ __('auth.username') }}</label>
                            <div class="mt-2">
                                <input id="username" name="username" type="text" placeholder="{{ __('auth.username') }}" value="{{ old('username') }}" required
                                    autocomplete="username"
                                    class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-700 sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div>
                            <label for="password"
                                class="block text-sm font-medium leading-6 text-white">{{ __('auth.password') }}</label>
                            <div class="mt-2">
                                <input id="password" name="password" type="password" placeholder="{{ __('auth.password') }}"
                                    autocomplete="current-password" required
                                    class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-700 sm:text-sm sm:leading-6">
                            </div>
                        </div>


                        <x-errors />

                        <div>
                            <x-button type="submit" class="w-full">{{ __('auth.signIn') }}</x-button>
                        </div>
                    </form>
                    <div>
                        <x-language-switcher />
                    </div>

                </div>
            </div>
        </div>
        <div class="relative hidden w-0 flex-1 lg:block">
            <img class="absolute inset-0 h-full w-full object-cover" src="{{ asset('images/app-proximo.png') }}"
                alt="">
        </div>
    </div>
@endsection
