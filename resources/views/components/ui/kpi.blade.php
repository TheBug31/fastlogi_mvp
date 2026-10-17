@props([
    'label',
    'icon' => null,
    'trend' => null,
    'trendDirection' => 'neutral',
])

@php
    $trendClasses = match ($trendDirection) {
        'up' => 'text-green-600',
        'down' => 'text-red-600',
        default => 'text-slate-500',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white shadow-sm p-6']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-slate-900">{{ $slot }}</p>
            @if ($trend)
                <p class="mt-1.5 text-xs {{ $trendClasses }}">{{ $trend }}</p>
            @endif
        </div>

        @if ($icon)
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-50 text-teal-700" aria-hidden="true">
                <x-icon :name="$icon" />
            </span>
        @endif
    </div>
</div>
