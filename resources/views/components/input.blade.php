<div class="mb-6 flex-1 max-w-2xl">
    @if (isset($label))
    <div class="flex">
        <x-label for="{{ $name }}">{{ $label }}</x-label>
        @if(isset($req) && $req)
        <div class="text-sm text-red-500 flex items-center justify-center">
            *
        </div>
        @endif
    </div>
    @endif

    <div class="flex flex-col gap-2">
        <input name="{{ $name }}" type="{{ $type ?? 'text' }}" id="queue-image-input"
            class="block min-h-9 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6
                @if (($type ?? 'text') === 'color') p-1 h-10 w-14 block bg-white border border-gray-200 cursor-pointer rounded-lg disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700
                @elseif (($type ?? 'text') === 'file')
                    w-full flex grow max-w-2xl py-2.5 px-3 file:mr-4 file:py-1.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-proximo-50 file:text-proximo-700 hover:file:bg-proximo-100
                @else
                    w-full max-w-2xl py-1.5 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none @endif"
            @if (($type ?? 'text') !== 'file') value="{{ $value ?? '' }}" @endif />

        @error($name)
            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
        @enderror
        
    </div>
</div>
