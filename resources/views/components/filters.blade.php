<div class="flex justify-center flex-col gap-2 w-full">
    <div class="flex gap-2 items-center w-full">
        <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-2 w-full">
            {{ $slot }}
        </form>
    </div>
</div>
