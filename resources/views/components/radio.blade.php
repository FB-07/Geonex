<div class="mb-6">
    <fieldset>
        @if (isset($label))
            <legend class="label">{{ $label }}</legend>
        @endif
        <div class="mt-2 flex items-center gap-8">
            @foreach ($options as $value => $text)
                <div class="flex items-center gap-x-3">
                    <input id="{{ $name }}-{{ $value }}" name="{{ $name }}" type="radio" value="{{ $value }}" class="h-4 w-4 border-gray-300 text-proximo-600 focus:ring-proximo-600"{{ ($selected == $value || (empty($selected) && ($value == 0 || $value === 'NO'))) ? 'checked' : '' }}>
                    <x-label for="{{ $name }}-{{ $value }}">{{ $text }}</x-label>
                </div>
            @endforeach
        </div>
    </fieldset>
</div>
