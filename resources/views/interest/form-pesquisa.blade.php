@extends('layouts/auth')

@section('breadcrumbs')
    <x-breadcrumbs class="mb-4" :links="[
        __('navbar.p_interest') => route('interest.index'),
        __('index.create') => route('interest.create'),
    ]" />
@endsection

@section('content')
    @if ($errors->any())
        <div class="mb-4 rounded bg-red-100 p-4 text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data class="h-full">
        <x-heroicon-o-information-circle class="hidden" />

        <form action="{{ route('interest.store') }}" method="POST" id="zone-form" enctype="multipart/form-data" class="flex flex-col h-[calc(100vh-150px)]">
            @csrf

            <div class="box flex-1 flex flex-col overflow-y-auto gap-4 custom-scrollbar overflow-x-visible">
                
                <x-input
                    name="nome"
                    label="{{ __('levels.name') }} do Ponto de interesse"
                    type="text"
                    value="{{ old('nome') }}"
                    required
                />

                <x-input
                    name="pesquisa"
                    label="Pesquisa"
                    type="text"
                    required
                />

                <x-input
                    name="color"
                    label="{{ __('levels.colour') }}"
                    type="color"
                    required
                    value="#10776f"
                />

                <div x-data="{ 
                    preview: null, 
                    selectedIcon: '{{ old('icon', 'location') }}',
                    setImage(event) {
                        this.preview = URL.createObjectURL(event.target.files[0]);
                        if (this.preview) this.selectedIcon = null; 
                    },
                    removeImage() {
                        this.preview = null;
                        this.$refs.fileInput.value = null;
                    },
                    selectIcon(icon) {
                        this.selectedIcon = icon;
                        if (icon) this.removeImage();
                    }
                }" class="flex flex-col gap-4">

                    <div class="flex flex-col gap-2">
                        <label class="block font-medium">Imagem</label>

                        <input 
                            type="file" 
                            name="image" 
                            accept="image/*"
                            @change="setImage($event)"
                            x-ref="fileInput"
                            class="block w-full text-sm text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded file:border-0
                                file:text-sm file:font-semibold
                                file:bg-proximo-700 file:text-white
                                hover:file:bg-proximo-900"
                        >

                        <template x-if="preview">
                            <div class="flex flex-col items-start gap-2 mt-2">
                                <img :src="preview" class="w-32 h-32 object-cover rounded border" alt="Preview da imagem">
                                <button type="button"
                                        @click="removeImage()"
                                        class="px-3 py-1 text-sm bg-red-500 text-white rounded hover:bg-red-600">
                                    Remover imagem
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="space-y-4">
                        <label class="block font-medium">Escolher Ícone</label>

                        <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-2 max-w-2xl overflow-y-auto border p-2 rounded">
                            @foreach($icons as $icon)
                                <button
                                    type="button"
                                    class="p-2 border rounded flex items-center justify-center transition"
                                    :class="selectedIcon === '{{ $icon }}'
                                        ? 'bg-proximo-500 border-proximo-600 text-white'
                                        : 'bg-proximo-700 hover:bg-proximo-900 text-white'"
                                    @click="selectIcon('{{ $icon }}')"
                                    title="{{ $icon }}"
                                >
                                    <x-dynamic-component :component="'ionicon-' . $icon" class="w-6 h-6"/>
                                </button>
                            @endforeach
                        </div>

                        <input type="hidden" name="icon" x-model="selectedIcon" />
                    </div>
                </div>

                <x-select label="Distrito" name="distrito" :options="[]" />
                <x-select label="Município" name="municipio" :options="[]" />
                <x-select label="Freguesia" name="freguesia" :options="[]" />

            </div>

            {{-- Footer --}}
            <div class="mt-8 flex items-center justify-end gap-x-4">
                <x-button-link class="bg-gray-700" :href="route('interest.index')">{{ __('index.cancel') }}</x-button-link>
                <x-button type="submit">{{ __('index.save') }}</x-button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        window.checkInterestUrl = "{{ route('interest.check') }}";
    </script>
    @vite('resources/js/pages/POI_form.js')
    @endpush
@endsection
