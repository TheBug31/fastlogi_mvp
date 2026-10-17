@props([
    'type' => 'info',
    'title' => null,
])

@php
    $map = [
        'warning' => ['classes' => 'border-amber-200 bg-amber-50 text-amber-900', 'icon' => 'exclamation-triangle', 'iconClass' => 'text-amber-500'],
        'error' => ['classes' => 'border-red-200 bg-red-50 text-red-800', 'icon' => 'exclamation-circle', 'iconClass' => 'text-red-500'],
        'info' => ['classes' => 'border-blue-200 bg-blue-50 text-blue-800', 'icon' => 'information-circle', 'iconClass' => 'text-blue-500'],
        'success' => ['classes' => 'border-green-200 bg-green-50 text-green-800', 'icon' => 'check-circle', 'iconClass' => 'text-green-500'],
    ];

    $entry = $map[$type] ?? $map['info'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border p-4 text-sm '.$entry['classes']]) }} role="{{ $type === 'error' ? 'alert' : 'status' }}">
    <x-icon :name="$entry['icon']" class="mt-0.5 shrink-0 {{ $entry['iconClass'] }}" />

    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        {{ $slot }}
    </div>

    @if (! empty($actions))
        <div class="shrink-0 self-center">
            {{ $actions }}
        </div>
    @endif
</div>
