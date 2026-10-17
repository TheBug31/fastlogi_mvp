@props([
    'label' => null,
    'name',
    'help' => null,
    'required' => false,
])

@php
    $error = $errors->first($name);

    $controlClasses = $error
        ? 'h-10 w-full rounded-lg border border-red-500 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20'
        : 'h-10 w-full rounded-lg border border-slate-300 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20';

    $control = $attributes->except(['wire:key', 'label', 'name', 'help', 'required']);
    $control = $control->merge(['class' => $controlClasses, 'name' => $name, 'id' => $attributes->get('id', $name)]);

    if ($required) {
        $control = $control->merge(['required' => 'required']);
    }

    if ($error) {
        $control = $control->merge(['aria-invalid' => 'true', 'aria-describedby' => $name.'-error']);
    }
@endphp

<div {{ $attributes->only('wire:key')->merge(['class' => 'w-full']) }}>
    @if ($label)
        <label for="{{ $attributes->get('id', $name) }}" class="mb-1.5 block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <select {{ $control }}>
        {{ $slot }}
    </select>

    @if ($help && ! $error)
        <p class="mt-1.5 text-xs text-slate-500">{{ $help }}</p>
    @endif

    @if ($error)
        <p id="{{ $name }}-error" class="mt-1.5 text-xs font-medium text-red-600">{{ $error }}</p>
    @endif
</div>
