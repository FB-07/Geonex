@props(['action', 'back'])

<form action="{{ $action }}" method="POST">
    @csrf
    @method('PUT')

    <div class="box flex flex-col">
        {{ $slot }}

        <div class="mt-8 flex items-center justify-end gap-x-4">
            <x-button-link type="button" class="bg-gray-700 hover:bg-gray-800"
                :href="$back">{{ __('technical.cancel') }}</x-button-link>
            <x-button type="submit">{{ __('technical.save') }}</x-button>
        </div>
    </div>
</form>
