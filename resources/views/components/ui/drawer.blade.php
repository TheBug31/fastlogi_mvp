@props([
    'title',
    'property' => null,
    'size' => 'md',
])

@php
    $titleId = 'drawer-title-'.preg_replace('/[^a-z0-9]+/i', '-', Str::slug($title));
    $width = $size === 'sm' ? 'max-w-[480px]' : 'max-w-[560px]';
@endphp

<div
    class="fixed inset-0 z-50 bg-slate-900/60"
    @if ($property)
        x-data="{
            open: true,
            previousFocus: null,
            init() {
                this.previousFocus = document.activeElement;
                this.$nextTick(() => {
                    const target = this.$refs.panel.querySelector('[autofocus], button, a, input');
                    if (target) target.focus();
                });
            },
            close() {
                $wire.set('{{ $property }}', false);
                const prev = this.previousFocus;
                this.$nextTick(() => { if (prev && typeof prev.focus === 'function') prev.focus(); });
            },
        }"
        @keydown.escape.window="close()"
        x-show="open"
        x-cloak
    @endif
>
    <div class="absolute inset-0" @if ($property) @click="close()" @endif aria-hidden="true"></div>

    <aside
        x-ref="panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $titleId }}"
        class="absolute inset-y-0 right-0 flex w-full {{ $width }} flex-col bg-slate-50 shadow-2xl"
    >
        <header class="flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-6 py-4">
            <div class="min-w-0">
                <h2 id="{{ $titleId }}" class="text-lg font-semibold tracking-tight text-slate-900">{{ $title }}</h2>
                @if (! empty($meta))
                    <div class="mt-1.5 flex flex-wrap items-center gap-2">
                        {{ $meta }}
                    </div>
                @endif
            </div>

            <button
                type="button"
                class="shrink-0 rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2"
                @if ($property) @click="close()" @else disabled @endif
                aria-label="Cerrar"
            >
                <x-icon name="x-mark" />
            </button>
        </header>

        <div class="flex-1 space-y-3 overflow-y-auto p-6">
            {{ $slot }}
        </div>

        @if (! empty($footer))
            <footer class="flex justify-end gap-3 border-t border-slate-200 bg-white px-6 py-4">
                {{ $footer }}
            </footer>
        @endif
    </aside>
</div>
