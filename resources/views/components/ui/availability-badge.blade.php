@props([
    'availability',
])

@php
    $map = [
        'available' => ['label' => 'Disponible', 'classes' => 'bg-green-100 text-green-800', 'dot' => 'bg-green-500'],
        'busy' => ['label' => 'Ocupado', 'classes' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-500'],
        'offline' => ['label' => 'Desconectado', 'classes' => 'bg-slate-100 text-slate-600', 'dot' => 'bg-slate-400'],
    ];

    $entry = $map[$availability] ?? $map['offline'];
@endphp

<span {{ $attributes->merge(['class' => trim('inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium '.$entry['classes'])]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $entry['dot'] }}" aria-hidden="true"></span>
    {{ $entry['label'] }}
</span>
