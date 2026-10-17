@props([
    'label' => null,
    'name',
    'help' => null,
    'type' => 'text',
    'required' => false,
    'invalid' => null,
    'showError' => true,
])

@php
    // Ojo: MessageBag::first() devuelve '' (no null) cuando el campo no tiene error.
    $message = $errors->has($name) ? $errors->first($name) : null;
    $isInvalid = $invalid ?? ($message !== null);
    $showMessage = $showError && $message !== null;

    $controlClasses = $isInvalid
        ? 'h-10 w-full rounded-lg border border-red-500 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20'
        : 'h-10 w-full rounded-lg border border-slate-300 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-600/20';

    if (isset($suffix)) {
        $controlClasses .= ' pr-10';
    }

    $control = $attributes->except(['wire:key', 'type', 'label', 'name', 'help', 'required']);
    $control = $control->merge([
        'class' => $controlClasses,
        'name' => $name,
        'id' => $attributes->get('id', $name),
        'type' => $type,
    ]);

    if ($required) {
        $control = $control->merge(['required' => 'required']);
    }

    if ($isInvalid) {
        $control = $control->merge(['aria-invalid' => 'true']);
    }

    if ($showMessage) {
        $control = $control->merge(['aria-describedby' => $name.'-error']);
    }
@endphp

<div {{ $attributes->only('wire:key')->merge(['class' => 'w-full']) }}>
    @if ($label)
        <label for="{{ $attributes->get('id', $name) }}" class="mb-1.5 block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <input {{ $control }} />

        @if (isset($suffix))
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                {{ $suffix }}
            </div>
        @endif
    </div>

    @if ($help && ! $showMessage)
        <p class="mt-1.5 text-xs text-slate-500">{{ $help }}</p>
    @endif

    @if ($showMessage)
        <p id="{{ $name }}-error" class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
    @endif
</div>
