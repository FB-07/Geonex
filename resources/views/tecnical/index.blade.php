@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.queues') => route('tecnical'),
    ]" />
@endsection
 
@section('content')
    <!--
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
    -->

    <div class="box mt-6">
        <div class="flex flex-col gap-4">
            <table class="min-w-full divide-y divide-gray-300">

                <!-- Pelo Id e tabela -->

                <thead>
                    <tr>
                        <th scope="col"
                            class="hidden py-3.5 pl-0 pr-3 text-left text-sm font-semibold text-gray-900 sm:table-cell">
                            ID
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 xl:table-cell">
                            NOME
                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 xl:table-cell">

                        </th>
                        <th scope="col"
                            class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 xl:table-cell">
                            AÇÕES
                        </th>
                    </tr>
                </thead>
                <tbody class="text-gray-500">
                    <tr>
                        <th class="hidden px-3 py-3.5 text-left text-sm xl:table-cell">
                            1
                        </th>
                        <th class="hidden px-3 py-3.5 text-left text-sm xl:table-cell">
                            Lisboa Lisboa Lisboa Lisboa Lisboa
                        </th>
                        <th class="hidden px-3 py-3.5 text-left text-sm xl:table-cell">

                        </th>
                        <th class="ml-3 flex items-center gap-2 whitespace-nowrap py-4 pr-0 text-sm font-medium">
                            <a href="" class="flex items-center gap-1 text-proximo-600 hover:text-proximo-900">
                                <x-heroicon-c-pencil-square class="w-4 h-4" />
                                <span>Editar</span>
                            </a>

                            <span class="text-gray-400">|</span>

                            <form action="" method="POST" class="inline">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex items-center gap-1 text-proximo-600 hover:text-red-600"
                                >
                                    <x-heroicon-c-minus-circle class="w-4 h-4" />
                                    <span>Apagar</span>
                                </button>
                            </form>
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
 