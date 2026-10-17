@props([
    'align' => 'left',
])

@php
    $classes = match ($align) {
        'right' => 'px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500',
        'center' => 'px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500',
        default => 'px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500',
    };
@endphp

<th {{ $attributes->merge(['class' => $classes, 'scope' => 'col']) }}>
    {{ $slot }}
</th>
