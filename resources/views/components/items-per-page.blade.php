<div class="flex items-center gap-2">
    <label for="{{ $name }}" class="text-sm font-semibold text-gray-900">
        {{ __('index.resultsPerPage') }}
    </label>

    <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-2">
        <input type="number" name="{{ $name }}" value="{{ session($name, $default) }}" min="1"
            max="100"
            class="border-gray-300 rounded px-2 py-1 text-sm w-11 focus:ring-0 focus:border-gray-300
              [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">

        <button type="submit" class="hidden"></button>
    </form>
</div>
