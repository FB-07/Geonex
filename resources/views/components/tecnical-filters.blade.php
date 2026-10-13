@props([
    'search' => null,
    'selects' => [],
])

@php
    $active = collect(['q', ...array_column($selects, 'name')])->contains(fn ($key) => request()->filled($key));
@endphp

<form action="{{ url()->current() }}" method="GET" class="flex flex-wrap items-center gap-2">
    @if ($search)
        <div class="relative">
            <x-heroicon-m-magnifying-glass class="pointer-events-none absolute left-2 top-2 h-5 w-5 text-gray-400" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $search }}"
                class="h-9 w-64 rounded-md border border-gray-300 pl-8 text-sm text-gray-900 placeholder:text-gray-400 focus:border-gray-300 focus:ring-0">
        </div>
    @endif

    @foreach ($selects as $i => $select)
        <div class="relative" x-data="{
                open: false,
                query: '',
                value: @js((string) request($select['name'], '')),
                options: @js(collect($select['options'])->map(fn ($label, $id) => ['id' => (string) $id, 'label' => $label])->values()),
                get label() { return this.options.find(o => o.id === this.value)?.label ?? '' },
                get filtered() {
                    const q = this.query.toLowerCase();
                    return this.options.filter(o => o.label.toLowerCase().includes(q)).slice(0, 100);
                },
                choose(id) {
                    this.value = id;
                    this.open = false;
                    this.$nextTick(() => {
                        this.$el.closest('form').querySelectorAll('[data-cascade-from]').forEach(el => {
                            if (Number(el.dataset.cascadeFrom) > {{ $i }}) el.value = '';
                        });
                        this.$el.closest('form').submit();
                    });
                },
            }" @click.outside="open = false" @keydown.escape="open = false">
            <input type="hidden" name="{{ $select['name'] }}" :value="value" data-cascade-from="{{ $i }}">

            <button type="button" @click="open = !open; $nextTick(() => open && $refs.search.focus())"
                class="flex h-9 w-52 items-center justify-between gap-2 rounded-md border border-gray-300 px-2 text-left text-sm text-gray-900">
                <span class="truncate" x-text="label || @js($select['label'] . ': ' . __('technical.all'))"
                    :class="label ? '' : 'text-gray-500'"></span>
                <x-heroicon-m-chevron-down class="h-4 w-4 shrink-0 text-gray-400" />
            </button>

            <div x-show="open" x-cloak
                class="absolute z-50 mt-1 w-64 rounded-md border border-gray-200 bg-white shadow-lg">
                <input x-ref="search" x-model="query" type="search" placeholder="{{ __('technical.search') }}"
                    class="block w-full rounded-t-md border-0 border-b border-gray-200 px-3 py-2 text-sm focus:ring-0">
                <ul class="max-h-60 overflow-y-auto py-1 text-sm">
                    <li>
                        <button type="button" @click="choose('')"
                            class="block w-full px-3 py-1.5 text-left text-gray-500 hover:bg-gray-100">
                            {{ $select['label'] }}: {{ __('technical.all') }}
                        </button>
                    </li>
                    <template x-for="o in filtered" :key="o.id">
                        <li>
                            <button type="button" @click="choose(o.id)" x-text="o.label"
                                :class="o.id === value ? 'bg-gray-100 font-semibold' : ''"
                                class="block w-full px-3 py-1.5 text-left text-gray-900 hover:bg-gray-100"></button>
                        </li>
                    </template>
                    <li x-show="filtered.length === 0" class="px-3 py-2 text-gray-500">{{ __('technical.empty') }}</li>
                </ul>
            </div>
        </div>
    @endforeach

    <x-button type="submit" class="h-9"><x-heroicon-m-magnifying-glass class="h-4 w-4" /></x-button>

    @if ($active)
        <a href="{{ url()->current() }}" class="flex items-center gap-1 text-sm text-red-600 hover:text-red-800">
            <x-heroicon-c-x-circle class="h-5 w-5" />
            {{ __('technical.clear') }}
        </a>
    @endif
</form>
