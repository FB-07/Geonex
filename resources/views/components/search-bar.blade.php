<div class="mx-4 h-6 w-px bg-gray-900/10" aria-hidden="true"></div>
<form class="relative mb-0 flex flex-1" action="#" method="GET">
    <label for="search-field" class="sr-only">{{ __('search.search') }}</label>
    <x-heroicon-m-magnifying-glass class="h-5 w-5 flex-shrink-0 text-gray-400" />
    <input id="search-field"
        class="block h-full w-full border-0 py-0 pl-5 pr-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
        placeholder="{{ __('search.search') }}" type="search" name="search">
</form>
