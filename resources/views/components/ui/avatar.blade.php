@props([
    'name',
    'size' => 'md',
])

@php
    $initials = collect(explode(' ', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');

    $classes = match ($size) {
        'sm' => 'h-8 w-8 text-xs',
        'lg' => 'h-12 w-12 text-base',
        default => 'h-10 w-10 text-sm',
    };
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center justify-center rounded-full border border-teal-200 bg-teal-50 font-semibold text-teal-800 {$classes}"]) }}
    aria-hidden="true"
>{{ $initials }}</span>
