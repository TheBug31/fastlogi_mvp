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
            previousFocus: null,
            focusableElements() {
                return [...this.$refs.panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]):not([type=hidden]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex=\'-1\'])')]
                    .filter((element) => element.getClientRects().length > 0);
            },
            trapFocus(event) {
                const elements = this.focusableElements();
                const first = elements[0];
                const last = elements[elements.length - 1];

                if (!first) {
                    event.preventDefault();
                    this.$refs.panel.focus();
                } else if (event.shiftKey && (document.activeElement === first || !this.$refs.panel.contains(document.activeElement))) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && (document.activeElement === last || !this.$refs.panel.contains(document.activeElement))) {
                    event.preventDefault();
                    first.focus();
                }
            },
            init() {
                this.previousFocus = document.activeElement;
                this.$nextTick(() => {
                    const target = this.$refs.panel.querySelector('[autofocus], button, a, input');
                    if (target) target.focus();
                });
            },
            close() {
                $wire.set('{{ $property }}', false);
                this.restoreFocus();
            },
            restoreFocus() {
                const prev = this.previousFocus;
                this.$nextTick(() => { if (prev && typeof prev.focus === 'function') prev.focus(); });
            },
        }"
        @keydown.escape.window="close()"
        @keydown.tab="trapFocus($event)"
    @endif
>
    <div class="absolute inset-0" @if ($property) @click="restoreFocus()" wire:click="$set('{{ $property }}', false)" @endif aria-hidden="true"></div>

    <aside
        x-ref="panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $titleId }}"
        tabindex="-1"
        class="absolute inset-y-0 right-0 flex w-full {{ $width }} flex-col bg-slate-50 shadow-2xl"
    >
        <header class="flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-4 py-4 sm:px-6">
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
                @if ($property) @click="restoreFocus()" wire:click="$set('{{ $property }}', false)" @else disabled @endif
                aria-label="Cerrar"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </header>

        <div class="flex-1 space-y-3 overflow-y-auto p-4 sm:p-6">
            {{ $slot }}
        </div>

        @if (! empty($footer))
            <footer class="flex flex-wrap justify-end gap-2 border-t border-slate-200 bg-white px-4 py-3 sm:gap-3 sm:px-6 sm:py-4">
                {{ $footer }}
            </footer>
        @endif
    </aside>
</div>
