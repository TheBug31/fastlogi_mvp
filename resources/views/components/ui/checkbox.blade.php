@props([
    'label' => null,
    'name',
    'checked' => false,
    'value' => '1',
])

@php
    $id = $attributes->get('id', $name);

    $control = $attributes->except(['label', 'name', 'checked', 'value', 'id'])->merge([
        'class' => 'h-4 w-4 rounded border-slate-300 accent-teal-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2',
        'type' => 'checkbox',
        'name' => $name,
        'id' => $id,
        'value' => $value,
    ]);

    // El controlador valida 'remember' con la regla boolean, que rechaza el valor
    // por defecto "on" de los checkbox sin value; por eso el valor va en "1".
    $isChecked = old($name, $checked);
@endphp

<label for="{{ $id }}" class="flex cursor-pointer select-none items-center gap-2">
    <input {{ $control }} @checked($isChecked) />

    @if ($label)
        <span class="text-sm text-slate-700">{{ $label }}</span>
    @endif
</label>
