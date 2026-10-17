@props([
    'align' => 'left',
    'emphasis' => false,
])

@php
    $alignClass = match ($align) {
        'right' => 'text-right',
        'center' => 'text-center',
        default => 'text-left',
    };

    $classes = trim('px-4 py-3.5 text-sm '.$alignClass.' '.($emphasis ? 'font-semibold text-slate-900' : 'text-slate-700'));
@endphp

<td {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</td>
