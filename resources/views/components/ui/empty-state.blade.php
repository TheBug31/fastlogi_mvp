@props([
    'icon' => 'inbox',
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500" aria-hidden="true">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>

    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm text-slate-500">{{ $description }}</p>
    @endif

    @if (! empty($action))
        <div class="mt-5">
            {{ $action }}
        </div>
    @endif
</div>
