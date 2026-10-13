@extends('layouts.app')

@php
    $user = Auth::user();
    use App\Support\Sections;
    $sections = Sections::all();
@endphp

@section('body')
@stack('scripts')
    <div x-data="{ open: true }" @keydown.window.escape="open = false" class="flex h-full flex-col">
        <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center bg-white text-gray-500 shadow-md">
            <div class="flex h-full w-80 shrink-0 items-center justify-between bg-proximo-800 px-6 py-2">
                <a class="flex shrink-0 cursor-pointer items-center pt-2" href="{{ route('map.index') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="Proximo Logo">
                </a>
                <button type="button" class="text-white" @click="open = !open">
                    <div x-show="open"><x-heroicon-o-x-mark class="h-5 w-5 flex-shrink-0" /></div>
                    <div x-show="!open"><x-heroicon-o-bars-3 class="h-5 w-5 flex-shrink-0" /></div>
                </button>
            </div>

            <div x-data="{ show: false }" class="flex h-full flex-1 items-center px-6">
                @yield('breadcrumbs')

                @yield('search')
                <div class="ml-auto flex items-center gap-x-4">

                    <!-- Separator -->
                    <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-900/10" aria-hidden="true"></div>

                    <div>
                        <select onchange="changeLanguage(this.value)" class="border-none focus:ring-0 text-sm">
                            <option value="en" {{ App::getLocale() == 'en' ? 'selected' : '' }}>English</option>
                            <option value="pt" {{ App::getLocale() == 'pt' ? 'selected' : '' }}>Português</option>
                        </select>
                    </div>

                    <div class="relative flex cursor-pointer items-center gap-4" @click="show = !show">
                        <div class="text-sm font-semibold text-gray-900">{{ $user->gfuser_alias }}</div>
                        <x-heroicon-o-chevron-down class="h-4 w-4 flex-shrink-0 text-gray-900 font-semibold" />
                    </div>

                    <div x-show="show"
                        class="absolute right-6 top-12 z-10 mt-2.5 w-36 origin-top-right rounded-md bg-white py-2 text-gray-900 shadow-md ring-1 ring-gray-900/5 focus:outline-none"
                        role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">

                        <a href="{{ url('/profile') }}"
                            class="flex items-center justify-between gap-2 px-3 py-1 text-sm leading-6">
                            {{ __('auth.profile') }}
                        </a>
                        <a href="{{ url('#') }}"
                            class="flex items-center justify-between gap-2 px-3 py-1 text-sm leading-6">
                            {{ __('auth.changePassword') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center justify-between gap-2 px-3 py-1 text-left text-sm leading-6">
                                {{ __('auth.logout') }}
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>
        <div class="flex h-[calc(100vh-4rem)]">
            <div x-show="open" class="relative flex w-80 shrink-0 bg-proximo-700 p-4" style="display: none;">
                <nav class="flex flex-1 flex-col overflow-y-auto">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="">
                                <a href="{{ url('/map') }}"
                                    class="group flex items-center gap-x-4 rounded-md p-2 text-base leading-6 text-white hover:bg-proximo-500 hover:text-white">
                                    <div class="flex h-8 w-8 items-center justify-center p-2">

                                        <x-heroicon-o-squares-2x2 class="h-5 w-5 flex-shrink-0" />

                                    </div>
                                    <h2>{{ __('navbar.dashboard') }}</h2>
                                </a>
                                @foreach ($sections as $section => $subsection)
                                    <div class="mt-8 text-sm leading-6 text-proximo-100">
                                        <h1 class="mb-2">{{ $section }}</h1>
                                        @foreach ($subsection as $route)
                                            <div x-data="{ isOpen: false }" class="text-sm leading-6 text-gray-100">
                                                <div
                                                    class="flex items-center justify-between rounded-md p-2 hover:bg-proximo-500 hover:text-white">

                                                    @if (isset($route['link']) && empty($route['sub-routes']))
                                                        <a href="{{ url('/' . $route['link']) }}"
                                                            class="group flex flex-1 items-center gap-x-4 text-base leading-6 text-white hover:bg-proximo-500 hover:text-white">
                                                            <div class="flex h-8 w-8 items-center justify-center p-2">
                                                                <x-dynamic-component :component="'heroicon-o-' . $route['icon']" class="h-5 w-5" />
                                                            </div>
                                                            <h2>{{ $route['name'] }}</h2>
                                                        </a>
                                                    @elseif (!empty($route['sub-routes']))
                                                        <div class="flex flex-1 cursor-pointer items-center gap-x-4 text-base leading-6"
                                                            @click="isOpen = !isOpen">
                                                            <div class="flex h-8 w-8 items-center justify-center p-2">
                                                                <x-dynamic-component :component="'heroicon-o-' . $route['icon']" class="h-5 w-5" />
                                                            </div>
                                                            <h2>{{ $route['name'] }}</h2>
                                                            <x-heroicon-o-chevron-down class="ml-auto h-5 w-5" />
                                                        </div>
                                                    @endif
                                                </div>

                                                @if (!empty($route['sub-routes']))
                                                    <div x-show="isOpen">
                                                        @foreach ($route['sub-routes'] as $sub)
                                                            <a href="{{ url('/' . $sub['link']) }}"
                                                                class="group ml-6 flex items-center gap-x-4 rounded-md p-2 text-base leading-6 text-white hover:bg-proximo-500 hover:text-white">
                                                                <div class="flex h-8 w-8 items-center justify-center p-2">
                                                                    <x-dynamic-component :component="'heroicon-o-' . $sub['icon']"
                                                                        class="h-5 w-5" />
                                                                </div>
                                                                <h2>{{ $sub['name'] }}</h2>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach

                                    </div>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </nav>

            </div>
            <div class="overflow-y-auto flex-1 p-6">
                @yield('content')
            </div>
        </div>
    </div>
    <script>
        function changeLanguage(lang) {
            window.location = '{{ url('change-language') }}/' + lang;
        }
    </script>
@endsection
