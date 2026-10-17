@props([
    'active' => 0,
    'max' => 1,
    'label' => null,
])

@php
    $max = max(1, (int) $max);
    $active = max(0, (int) $active);
    $percent = min(100, round(($active / $max) * 100));

    // Regla de la guía: teal < 70 %, ámbar >= 70 %, rojo al 100 %.
    $fillClass = match (true) {
        $percent >= 100 => 'bg-red-500',
        $percent >= 70 => 'bg-amber-500',
        default => 'bg-teal-600',
    };

    $text = $label ?? "{$active}/{$max} pedidos";
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="flex items-center justify-between gap-2">
        <span class="text-xs font-medium tabular-nums text-slate-600">{{ $text }}</span>
    </div>
    <div class="mt-1.5 h-1.5 w-full rounded-full bg-slate-200" role="progressbar" aria-valuenow="{{ $active }}" aria-valuemin="0" aria-valuemax="{{ $max }}" aria-label="{{ $text }}">
        <div class="h-1.5 rounded-full {{ $fillClass }}" style="width: {{ $percent }}%"></div>
    </div>
</div>
