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
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4"
    @if ($property)
        x-data="{
            open: true,
            previousFocus: null,
            init() {
                this.previousFocus = document.activeElement;
                this.$nextTick(() => {
                    const target = this.$refs.panel.querySelector('[autofocus], input:not([type=hidden]), select, textarea, button');
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
    @endif
>
    <!-- Fondo: clic cierra -->
    <div class="absolute inset-0" @if ($property) @click="close()" @endif aria-hidden="true"></div>

    <div
        x-ref="panel"
        @if ($property) x-cloak @endif
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $titleId }}"
        class="relative w-full {{ $panelWidth }} overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
    >
        <div class="flex items-start justify-between border-b border-slate-100 px-6 py-5">
            <div class="min-w-0">
                <h2 id="{{ $titleId }}" class="text-xl font-bold tracking-tight text-slate-900">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>

            <button
                type="button"
                class="ml-4 -mr-1 rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2"
                @if ($property) @click="close()" @else disabled @endif
                aria-label="Cerrar"
            >
                <x-icon name="x-mark" />
            </button>
        </div>

        <div class="max-h-[calc(100vh-210px)] space-y-5 overflow-y-auto p-6">
            {{ $slot }}
        </div>

        @if (! empty($footer))
            <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
