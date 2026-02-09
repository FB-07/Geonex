@props([
    'editData' => '#',
    'deleteData' => '#',
])

<td class="px-3 py-4">
    <div class="flex items-center gap-2 whitespace-nowrap text-sm font-medium">
        <a href="{{ $editData }}" class="flex items-center gap-1 text-proximo-600 hover:text-proximo-900">
            <x-heroicon-c-pencil-square class="w-4 h-4" />
            <span>Editar</span>
        </a>

        <span class="text-gray-400">|</span>

        <form action="{{ $deleteData }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex items-center gap-1 text-proximo-600 hover:text-red-600">
                <x-heroicon-c-minus-circle class="w-4 h-4" />
                <span>Apagar</span>
            </button>
        </form>
    </div>
</td>
