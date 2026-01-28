@extends('layouts.app')

@section('content')

    <div class="flex min-h-full">
        <div class="flex flex-1 flex-col justify-center bg-proximo-600 px-4 py-12 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <div>
                    <h2 class="mt-8 text-2xl font-bold leading-9 tracking-tight text-white">
                        {{ __('auth.signInToYourAccount') }}
                    </h2>
                    {{-- <p class="mt-2 text-sm leading-6 text-gray">
                        Not registered yet?
                        <a href="#" class="font-semibold text-proximo-700 hover:text-proximo-700">Create an account</a>
                    </p> --}}
                </div>

                <div class="mt-10 flex flex-col gap-4">

                    <form action={{ route('login') }} method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="username"
                                class="block text-sm font-medium leading-6 text-white">{{ __('auth.username') }}</label>
                            <div class="mt-2">
                                <input id="username" name="username" type="text" placeholder="Username" value="vsimoes"
                                    autocomplete="username"
                                    class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-700 sm:text-sm sm:leading-6">
                                @error('email')
                                    <p class="text-danger mt-2 text-xs">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="password"
                                class="block text-sm font-medium leading-6 text-white">{{ __('auth.password') }}</label>
                            <div class="mt-2">
                                <input id="password" name="password" type="password" placeholder="Password" value="ola"
                                    autocomplete="current-password" required
                                    class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-700 sm:text-sm sm:leading-6">
                                @error('password')
                                    <p class="text-danger mt-2 text-xs">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>


                        <x-errors />

                        {{-- <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember-me" name="remember-me" type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-proximo-700 focus:ring-proximo-700">
                                    <label for="remember-me" class="ml-3 block text-sm leading-6 text-white">Remember
                                        me</label>
                                </div>

                                <div class="text-sm leading-6">
                                    <a href="#" class="font-semibold text-proximo-700 hover:text-proximo-700">Forgot
                                        password?</a>
                                </div>
                            </div> --}}

                        <div>
                            <x-button type="submit" class="w-full">{{ __('auth.signIn') }}</x-button>
                        </div>
                    </form>
                    <div>
                        <select onchange="changeLanguage(this.value)"
                            class="block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-700 sm:text-sm sm:leading-6">
                            <option value="en" {{ App::getLocale() == 'en' ? 'selected' : '' }}>English</option>
                            <option value="pt" {{ App::getLocale() == 'pt' ? 'selected' : '' }}>Português</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>
        <div class="relative hidden w-0 flex-1 lg:block">
            <img class="absolute inset-0 h-full w-full object-cover" src={{ asset('images/app-proximo.png') }}
                alt="">
        </div>
    </div>
    <script>
        function changeLanguage(lang) {
            window.location = '{{ url('change-language') }}/' + lang;
        }
    </script>
