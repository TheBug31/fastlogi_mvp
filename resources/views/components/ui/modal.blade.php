@props([
    'title',
    'subtitle' => null,
    'property',
    'size' => 'default',
])

@php
    $panelWidth = $size === 'sm' ? 'max-w-[440px]' : 'max-w-[560px]';
    $titleId = 'modal-title-'.preg_replace('/[^a-z0-9]+/i', '-', Str::slug($title));
@endphp

<div
    class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 p-0 sm:items-center sm:p-4"
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
                    const target = this.$refs.panel.querySelector('[autofocus], input:not([type=hidden]), select, textarea, button');
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
    <!-- Fondo: clic cierra -->
    <div class="absolute inset-0" @if ($property) @click="restoreFocus()" wire:click="$set('{{ $property }}', false)" @endif aria-hidden="true"></div>

    <div
        x-ref="panel"
        role="dialog"
        tabindex="-1"
        aria-modal="true"
        aria-labelledby="{{ $titleId }}"
        class="relative w-full {{ $panelWidth }} max-h-[100dvh] overflow-hidden rounded-t-2xl border border-slate-200 bg-white shadow-2xl sm:max-h-[calc(100dvh-2rem)] sm:rounded-2xl"
    >
        <div class="flex items-start justify-between border-b border-slate-100 px-4 py-4 sm:px-6 sm:py-5">
            <div class="min-w-0">
                <h2 id="{{ $titleId }}" class="text-xl font-bold tracking-tight text-slate-900">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>

            <button
                type="button"
                class="ml-4 -mr-1 rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2"
                @if ($property) @click="restoreFocus()" wire:click="$set('{{ $property }}', false)" @else disabled @endif
                aria-label="Cerrar"
            >
                <x-icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>

        <div class="max-h-[calc(100dvh-150px)] space-y-5 overflow-y-auto p-4 sm:max-h-[calc(100dvh-210px)] sm:p-6">
            {{ $slot }}
        </div>

        @if (! empty($footer))
            <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 bg-slate-50 px-4 py-3 sm:gap-3 sm:px-6 sm:py-4">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
