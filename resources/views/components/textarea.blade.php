<div class="mb-6">
    @if (isset($label))
    <div class="flex">
        <x-label for="{{ $name }}">{{ $label }}</x-label>
        @if(isset($req) && $req)
        <div class="text-sm text-red-500 flex items-center justify-center">
            *
        </div>
        @endif
    </div>
    @endif
    <div class="flex w-full">
    <textarea name="{{ $name }}" type="{{ $type ?? 'text' }}" id="{{ $name }}" rows="{{ $rows ?? 3 }}"
    class="block w-full max-w-2xl rounded-md border-0 py-1 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-proximo-600 text-sm leading-6"
    value="{{ $value ?? '' }}"></textarea>
    
        @if(strpos($name, 'timeon'))
        <div class="text-sm text-gray-400 inline-block break-words w-fit ml-3">
            <div><strong>Dias:</strong> 1=dom … 7=sáb</div>
        
            <div><strong>Separadores:</strong></div>
            <ul class="list-disc pl-4">
                <li><b>;</b> (dias)</li>
                <li><b>+</b> (períodos)</li>
                <li><b>-</b> (entre horas)</li>
            </ul>
        
            <div><strong>Exemplo:</strong></div>
            <div class="break-all">
                1,09:00-17:00;3,09:00-13:00+14:00-18:00
            </div>
        </div>
        @elseif(strpos($name, 'vacation'))
        <div class="text-sm text-gray-400 inline-block break-words w-fit ml-3">
            <div><strong>Mês:</strong> 1=janeiro … 12=dezembro</div>
            <div><strong>Dia:</strong> de 01 a 31</div>   
            <div><strong>Separadores:</strong></div>
            <ul class="list-disc pl-4">
                <li><b>;</b> (entre periodos de férias)</li>
                <li><b>-</b> (x até y)</li>
            </ul>
        
            <div><strong>Exemplo:</strong></div>
            <div class="break-all">
                0801-08-31;1220-1231
            </div>
        </div>
    @endif
    
    
    </div>
    @error($name)
        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
    @enderror
</div>
