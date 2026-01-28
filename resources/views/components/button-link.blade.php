@php
$numberHover = null;
$string = null;

if(!empty($color)) {
    preg_match('/([a-zA-Z\-]+)(\d+)/', $color, $matches);

    if($matches) {
        $string = $matches[1];
        $number = (int) $matches[2]; 

        $numberHover = match(true) {
            $number > 100 && $number < 900 => $number + 100,
            $number >= 900 => $number - 100,
            default => $number + 50,
        };
        $hover = $string . $numberHover;
    }
}
@endphp
<a {{ $attributes->merge([
    'class' => 'rounded-md shadow-md px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm  focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-proximo-800 text-center w-32 flex items-center justify-center'
        . (!empty($color) ? ' ' . $color . ' ' : ' bg-proximo-800 ') . (!empty($hover) ? 'hover:'. $hover : 'hover:bg-proximo-900')
]) }}
    href="{{ $href }}">
    {{ $slot }}</a>
