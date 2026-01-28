<div class="flex-1">
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
    <div class="max-w-xl">
        <select name="{{ $name }}" id="{{ $name }}"
            class="block w-full min-h-9 rounded-md border-0 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6">
            <option value="" disabled {{ old($name, $selected ?? '') == '' ? 'selected' : '' }}>
                @if(isset($placeholder)) {{ $placeholder }}@else{{ __('components.selectOption') }}@endif
            </option>

            @foreach ($options as $value => $text)
                <option value="{{ $value }}" {{ old($name, $selected ?? '') == $value ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        </select>
    </div>
</div>
