@props([
    'padding' => 'default',
])

@php
    $classes = match ($padding) {
        'none' => 'rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden',
        'dense' => 'rounded-xl border border-slate-200 bg-white shadow-sm p-5',
        default => 'rounded-xl border border-slate-200 bg-white shadow-sm p-6',
    };
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
