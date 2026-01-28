<div id="filters-root" class="flex items-center h-10 gap-x-2 w-full">
    <span class="text-sm max-w-fit w-full flex">Filtrar por:</span>

    <select id="main-filter" class="filter-select max-w-[170px] text-sm p-1 px-2 rounded-md block w-full h-9  border border-gray-300 text-gray-900 outline-none focus:ring-0 focus:border-gray-300">
        <option value="" disabled selected>Selecionar filtro</option>
        @foreach($options as $label => $data)
            <option value="{{ $label }}">{{ $label }}</option>
        @endforeach
    </select>

    <div class="flex items-center w-full h-10 gap-2">
        <div id="area-select" class="flex items-center w-fit h-10 gap-2">
            @foreach($options as $label => $data)
                @if(request()->has($data[0]))
                <x-heroicon-o-chevron-right class="h-7 w-7 text-gray-400"/>
                    <div class="flex items-center gap-1 shadow-sm">

                        <select
                            id="{{ $label }}"
                            name="{{ $data[0] }}"
                            class="filter-select max-w-[170px] text-sm p-1 px-2 rounded-l-md block w-full h-9  border border-gray-300 text-gray-900 outline-none focus:ring-0 focus:border-gray-300
                            border-r-0"
                            
                        >
                            <option disabled>{{ $label }}</option>

                            @foreach($data[1] as $id => $desc)
                                <option value="{{ $id }}" @selected(request($data[0]) == $id)>
                                    {{ $desc }}
                                </option>
                            @endforeach
                        </select>

                        <button
                            type="button"
                            data-filter="{{ $label }}"
                            class="remove-filter w-9 ml-[-10px] h-9 rounded-r-md border border-gray-300
                            focus:outline focus:outline-2 focus:outline-proximo-600 focus:outline-offset-[-2px] text-red-500"
                        >
                            x
                        </button>

                    </div>
                @endif
            @endforeach

        </div>
        <div class="flex gap-2 items-center">
            <x-button id="some-button" class="hidden w-[50px] h-9"><x-heroicon-m-magnifying-glass class="" /></x-button>
        </div>        
    </div>
</div>
<script>
    const options = @json($options);
    const mainFilter = document.getElementById('main-filter');
    const areaSelect = document.getElementById('area-select');
    const someButton = document.getElementById('some-button');
    
    function toggleButton() {
        const activeSelects = areaSelect.querySelectorAll('select');
        someButton.classList.toggle('hidden', activeSelects.length === 0);
    }
    
    // Desativar opções já selecionadas
    document.querySelectorAll('#area-select select').forEach(sel => {
        const label = sel.querySelector('option').textContent; 
        const opt = mainFilter.querySelector(`option[value="${label}"]`);
        if(opt) opt.disabled = true;
    });
    
    toggleButton();
    
    mainFilter.addEventListener('change', () => {
        const dash = document.createElement('div');
        dash.innerHTML = `<x-heroicon-o-chevron-right class="h-7 w-7 text-gray-400"/>`;
        const label = mainFilter.value;
        if (!options[label]) return;
    
        const [name, values] = options[label];
        mainFilter.querySelector(`option[value="${label}"]`).disabled = true;
        mainFilter.value = '';
    
        const container = document.createElement('div');
        container.className = 'flex items-center gap-2';
        
        const select = document.createElement('select');
        select.name = name;
        select.className = 'filter-select max-w-[170px] text-sm p-1 px-2 rounded-l-md block w-full h-9 border border-gray-300 text-gray-900 outline-none focus:ring-0 focus:border-gray-300 border-r-0';
    
        const title = document.createElement('option');
        title.disabled = true;
        title.selected = true;
        title.textContent = label;
        select.appendChild(title);
    
        Object.entries(values).forEach(([id, desc]) => {
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = desc;
            select.appendChild(opt);
        });
    
        const btnRemove = document.createElement('button');
        btnRemove.type = 'button';
        btnRemove.textContent = 'x';
        btnRemove.className = 'w-9 ml-[-10px] h-9 rounded-r-md border border-gray-300 focus:outline focus:outline-2 focus:outline-proximo-600 focus:outline-offset-[-2px] text-red-500';
    
        btnRemove.addEventListener('click', () => {
            const paramName = select.name;
            const url = new URL(window.location.href);
    
            if (url.searchParams.has(paramName)) {
                url.searchParams.delete(paramName);
                window.location.href = url.toString();
                return;
            }
    
            container.remove();
            mainFilter.querySelector(`option[value="${label}"]`).disabled = false;
            toggleButton();
        });
    
        container.appendChild(dash);
        container.appendChild(select);
        container.appendChild(btnRemove);
        areaSelect.appendChild(container);
    
        toggleButton();
    });
    
    // Remove filtros já existentes
    document.querySelectorAll('.remove-filter').forEach(btn => {
        btn.addEventListener('click', e => {
            const container = e.target.closest('div');
            const select = container.querySelector('select');
            const paramName = select ? select.name : null;
    
            if(paramName) {
                const url = new URL(window.location.href);
                if (url.searchParams.has(paramName)) {
                    url.searchParams.delete(paramName);
                    window.location.href = url.toString();
                    return;
                }
            }
    
            container.remove();
            const label = e.target.dataset.filter;
            const opt = mainFilter.querySelector(`option[value="${label}"]`);
            if(opt) opt.disabled = false;
            toggleButton();
        });
    });
    </script>
    