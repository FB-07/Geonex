<select aria-label="{{ __('auth.language') }}" onchange="window.location = '{{ url('change-language') }}/' + this.value"
    {{ $attributes->merge(['class' => 'block w-full rounded-md border-0 py-1.5 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-proximo-700 sm:text-sm sm:leading-6']) }}>
    <option value="en" @selected(app()->getLocale() === 'en')>English</option>
    <option value="pt" @selected(app()->getLocale() === 'pt')>Português</option>
</select>
