@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.queues') => route('tecnical'),
    ]" />
@endsection
 
@section('search')
    <x-search-bar />
@endsection
 
@section('content')
    <div class="mb-6 flex items-center gap-2">
        <div class="flex flex-col gap-y-2 w-full">
            filtros se quiseres
        </div>
        <div class="flex gap-3 mt-auto ml-auto ">
            <x-button-link class="" href="a">
                {{ __('queues.newQueue') }}
            </x-button-link>
        </div>
    </div>
 
    <div class="box mt-6">
        <div class="flex flex-col gap-4">
            <table class="min-w-full divide-y divide-gray-300">
                <thead>
                    <tr>
                        <th scope="col"
                            class="hidden py-3.5 pl-0 pr-3 text-left text-sm font-semibold text-gray-900 sm:table-cell">
                            {{ __('queues.id') }}
                        </th>
                        <th scope="col"
                            class="max-w-48 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:max-w-none">
                            {{ __('queues.description') }}
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 xl:table-cell">
                            {{ __('queues.letter') }}
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 xl:table-cell">
                            {{ __('queues.site') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- @forelse ($queues as $queue)
                        <tr>
                            <td
                                class="hidden whitespace-nowrap py-4 pl-0 pr-3 text-sm font-medium text-gray-900 sm:table-cell">
                                {{ $queue->gfqueue_id }}</td>
                            <td
                                class="max-w-48 overflow-hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 sm:max-w-none">
                                {{ $queue->gfqueue_desc }}
                            </td>
                            <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 xl:table-cell">
                                {{ $queue->gfqueue_letter }}
                            </td>
                            <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 xl:table-cell">
                                {{ optional($sites->firstWhere('gfsite_id', $queue->gfqueue_gfsite_id))->gfsite_desc }}
                            </td>
                                                 
                            <td class="ml-3 flex justify-end gap-2 whitespace-nowrap py-4 pr-0 text-sm font-medium">
                                <a href="{{ route('queues.edit', ['queue' => $queue->gfqueue_id]) }}"
                                    class="text-proximo-600 hover:text-proximo-900">
                                    {{ __('index.edit') }}</a>
                                <div>|</div>
                                <a href="{{ route('terminalQueues.edit', ['queue' => $queue->gfqueue_id, 'where' => 'fromEdit']) }}"
                                    class="text-proximo-600 hover:text-proximo-900">
                                    Editar terminais</a>
                                <div>|</div>
                                <form action="{{ route('queues.destroy', ['queue' => $queue->gfqueue_id]) }}"
                                    method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-proximo-600 hover:text-proximo-900">
                                        {{ __('index.delete') }}
                                    </button>
                                </form>
 
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="flex justify-center items-center text-sm py-2">
                                    Não existe filas associadas a este terminal
                                </div>
                            </td>
                        </tr>
                    @endforelse --}}
                </tbody>
            </table>
            {{-- {{ $queues->links() }}
        </div class="overflow-x-auto"> --}}
    </div>
@endsection
 