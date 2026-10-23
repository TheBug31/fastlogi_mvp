@props([
    'color' => 'slate',
])

@php
    // Mapa literal completo: nunca concatenar 'bg-'.$color.'-100' (regla de la guía).
    $palette = [
        'slate' => 'bg-slate-200 text-slate-800',
        'slate-soft' => 'bg-slate-100 text-slate-600',
        'violet' => 'bg-violet-100 text-violet-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'teal' => 'bg-teal-100 text-teal-800',
        'amber' => 'bg-amber-100 text-amber-800',
        'green' => 'bg-green-100 text-green-800',
        'emerald' => 'bg-green-100 text-green-800',
        'red' => 'bg-red-100 text-red-700',
    ];

    $dot = [
        'slate' => 'bg-slate-500',
        'slate-soft' => 'bg-slate-400',
        'violet' => 'bg-violet-500',
        'blue' => 'bg-blue-500',
        'teal' => 'bg-teal-500',
        'amber' => 'bg-amber-500',
        'green' => 'bg-green-500',
        'emerald' => 'bg-green-500',
        'red' => 'bg-red-600',
    ];

    $classes = trim('inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium '.($palette[$color] ?? $palette['slate']));
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if ($dot[$color] ?? false)
        <span class="h-1.5 w-1.5 rounded-full {{ $dot[$color] }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
