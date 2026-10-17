@props([
    'active' => false,
    'count' => null,
    'xActive' => null,
])

@php
    $base = 'flex items-center gap-2 border-b-2 pb-3 text-sm font-medium whitespace-nowrap';
    $activeClasses = $base.' border-teal-600 text-teal-700';
    $inactiveClasses = $base.' border-transparent text-slate-500 hover:text-slate-800';

    $countBase = 'rounded-full px-2 py-0.5 text-xs font-semibold';
    $countActive = $countBase.' bg-teal-50 text-teal-700';
    $countInactive = $countBase.' bg-slate-100 text-slate-600';
@endphp

<button
    type="button"
    role="tab"
    @if ($xActive !== null)
        x-bind:class="{{ $xActive }} ? '{{ $activeClasses }}' : '{{ $inactiveClasses }}'"
        x-bind:aria-selected="{{ $xActive }} ? 'true' : 'false'"
    @else
        class="{{ $active ? $activeClasses : $inactiveClasses }}"
        @if ($active) aria-selected="true" @endif
    @endif
    {{ $attributes->except('class') }}
>
    {{ $slot }}

    @if ($count !== null)
        <span @if ($xActive !== null) x-bind:class="{{ $xActive }} ? '{{ $countActive }}' : '{{ $countInactive }}'" @else class="{{ $active ? $countActive : $countInactive }}" @endif>{{ $count }}</span>
    @endif
</button>
