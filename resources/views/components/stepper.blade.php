@props(['currentStepNumber', 'currentStepName', 'totalSteps'])

<div aria-label="Progress" class="mb-6">
    <ol role="list" class="space-y-4 md:flex md:space-y-0 md:space-x-8">
        @for ($i = 1; $i <= $totalSteps; $i++)
            @php
                $isCompleted = $i < $currentStepNumber;
                $isCurrent = $i == $currentStepNumber;
                $isUpcoming = $i > $currentStepNumber;
            @endphp

            <li class="md:flex-1">
                <div @class([
                    'flex flex-col border-l-4 py-2 pl-4 md:border-l-0 md:border-t-4 md:pt-4 md:pb-0 md:pl-0',
                    'border-proximo-600 dark:border-proximo-500' => $isCompleted || $isCurrent,
                    'border-gray-200 dark:border-white/10' => $isUpcoming,
                ])>
                    <span @class([
                        'text-sm font-medium',
                        'text-proximo-600 dark:text-proximo-400' => $isCompleted || $isCurrent,
                        'text-gray-500 dark:text-gray-400' => $isUpcoming,
                    ])>
                        {{ __('index.step') }} {{ $i }}
                    </span>

                    @if ($isCurrent)
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $currentStepName }}
                        </span>
                    @else
                        <span class="text-sm font-medium">&nbsp;</span>
                    @endif
                </div>
            </li>
        @endfor
    </ol>
</div>
