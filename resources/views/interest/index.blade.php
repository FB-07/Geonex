@extends('layouts/auth')
 
@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.places') => route('places'),
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
          <div class="box mt-2 px-3 pb-4">
            <div class="flex flex-col gap-4"></div>
    <table class="min-w-full divide-y divide-gray-300 table-fixed">
  <thead>
    <tr>
      <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Item</th>
      <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Ação</th>
    </tr>
  </thead>

  <tbody class="divide-y divide-gray-200 text-gray-500">

    <tr>
      <td class="px-3 py-3.5 text-sm">item1</td>
      <td class="px-3 py-3.5 text-sm">&gt;</td>
    </tr>

    <!-- ITEM EXPANSÍVEL (SÓ HTML) -->
    <tr>
      <td colspan="2" class="px-0 py-0">
        <details class="group">
          <!-- “linha” clicável -->
          <summary class="list-none cursor-pointer px-3 py-3.5 text-sm flex items-center justify-between">
            <span class="font-medium text-gray-900">item2</span>

            <!-- seta (troca só com CSS/Tailwind) -->
            <span class="text-gray-500 group-open:hidden">&gt;</span>
            <span class="text-gray-500 hidden group-open:inline">↓</span>
          </summary>

          <!-- conteúdo que aparece ao abrir -->
          <div class="box mt-2 px-3 pb-4">
            <div class="flex flex-col gap-4">
              <table class="min-w-full divide-y divide-gray-300 table-fixed">
                <thead>
                  <tr>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Head1</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Head2</th>
                  </tr>
                </thead>
                <tbody class="text-gray-500">
                  <tr>
                    <td class="px-3 py-3.5 text-sm">Body1</td>
                    <td class="px-3 py-3.5 text-sm">Body2</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </details>
      </td>
    </tr>

    <tr>
      <td class="px-3 py-3.5 text-sm">item3</td>
      <td class="px-3 py-3.5 text-sm">&gt;</td>
    </tr>

    <tr>
      <td class="px-3 py-3.5 text-sm">item4</td>
      <td class="px-3 py-3.5 text-sm">&gt;</td>
    </tr>

    <tr>
      <td class="px-3 py-3.5 text-sm">item5</td>
      <td class="px-3 py-3.5 text-sm">&gt;</td>
    </tr>

  </tbody>
</table>
</div>
          </div>

@endsection
 