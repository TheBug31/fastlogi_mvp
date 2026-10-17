@props([
    'status',
])

@php
    $map = [
        'pending' => ['label' => 'Pendiente', 'classes' => 'bg-slate-200 text-slate-800', 'dot' => 'bg-slate-500'],
        'offered' => ['label' => 'Ofrecido', 'classes' => 'bg-violet-100 text-violet-800', 'dot' => 'bg-violet-500'],
        'assigned' => ['label' => 'Asignado', 'classes' => 'bg-blue-100 text-blue-800', 'dot' => 'bg-blue-500'],
        'in_transit' => ['label' => 'En camino', 'classes' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-500'],
        'delivered' => ['label' => 'Entregado', 'classes' => 'bg-green-100 text-green-800', 'dot' => 'bg-green-500'],
        'cancelled' => ['label' => 'Cancelado', 'classes' => 'bg-slate-100 text-slate-500', 'dot' => 'bg-slate-400'],
        'incident' => ['label' => 'Incidencia', 'classes' => 'bg-red-100 text-red-700', 'dot' => 'bg-red-600'],
    ];

    // Los modelos emiten el enum (OrderStatus); también se acepta su valor en texto.
    $key = $status instanceof \BackedEnum ? $status->value : (string) $status;

    $entry = $map[$key] ?? $map['pending'];
@endphp

<span {{ $attributes->merge(['class' => trim('inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium '.$entry['classes'])]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $entry['dot'] }}" aria-hidden="true"></span>
    {{ $entry['label'] }}
</span>
