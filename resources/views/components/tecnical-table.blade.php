<table class="min-w-full divide-y divide-gray-300">
    <thead>
        <tr>
            {{ $head }}
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 uppercase">
                {{ __('technical.actions') }}
            </th>
        </tr>
    </thead>

    <tbody class="text-gray-500">
        {{ $slot }}
    </tbody>
</table>
