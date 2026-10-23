@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-start justify-between gap-4']) }}>
    <div class="min-w-0">
        <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
        @endif
    </div>

    @if (! empty($actions))
        <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
