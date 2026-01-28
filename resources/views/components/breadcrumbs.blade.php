<nav class="hidden lg:flex" aria-label="Breadcrumb">
    <ol role="list" class="flex items-center space-x-4">
        <li>
            <div>
                <a href="{{ url('/') }}" class="text-gray-400 hover:text-gray-500">
                    <x-heroicon-m-home class="h-5 w-5 flex-shrink-0 text-gray-400" />
                    <span class="sr-only">Home</span>
                </a>
            </div>
        </li>
        @foreach ($links as $label => $link)
            <li>
                <div class="flex items-center">

                    <x-heroicon-m-chevron-right class="h-5 w-5 flex-shrink-0 text-gray-400" />
                    <a href="{{ $link }}"
                        class="ml-4 text-sm text-gray-500 hover:text-gray-700">{{ $label }}</a>
                </div>
            </li>
        @endforeach
    </ol>
</nav>
