@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-start justify-between gap-4']) }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
        @endif
    </div>

    @if (! empty($actions))
        <div class="flex shrink-0 items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
