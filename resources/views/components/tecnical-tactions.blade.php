@props([
    'editData' => '#',
    'deleteData' => '#',
])


<div class="flex items-center gap-2 whitespace-nowrap text-sm font-medium">
    <a href="{{ $editData }}" class="flex items-center gap-1 text-proximo-600 hover:text-proximo-900">
        <x-heroicon-c-pencil-square class="w-4 h-4" />
        <span>Editar</span>
    </a>

    <span class="text-gray-400">|</span>
        <a href="#" class="flex items-center gap-1 text-proximo-600 hover:text-green-500">
            <x-heroicon-c-plus-circle class="w-4 h-4" />
            <span>Adicionar</span>
        </a>
    <span class="text-gray-400">|</span>

    <form action="{{ $deleteData }}" method="POST" onsubmit="return confirm('Remover este local do ponto de interesse?')" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="flex items-center gap-1 text-proximo-600 hover:text-red-600">
            <x-heroicon-c-minus-circle class="w-4 h-4" />
            <span>Apagar</span>
        </button>
    </form>
</div>
