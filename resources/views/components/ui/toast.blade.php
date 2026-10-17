@props([
    'message' => null,
    'type' => 'success',
])

@php
    // Si no se pasa message, toma el flash de éxito de la sesión (patrón de los componentes Livewire).
    $text = $message ?? session('success');

    $iconMap = [
        'success' => ['name' => 'check-circle', 'class' => 'text-green-600'],
        'error' => ['name' => 'exclamation-circle', 'class' => 'text-red-600'],
        'info' => ['name' => 'information-circle', 'class' => 'text-blue-600'],
    ];

    $entry = $iconMap[$type] ?? $iconMap['success'];
@endphp

@if ($text)
    <div
        x-data="{
            visible: true,
            text: @js($text),
            hideAfter() {
                clearTimeout(this._t);
                this._t = setTimeout(() => { this.visible = false; }, 4000);
            },
            init() {
                this.hideAfter();
                this._onToast = (e) => {
                    const message = e.detail?.message ?? e.detail;
                    if (typeof message === 'string' && message) this.text = message;
                    this.visible = true;
                    this.hideAfter();
                };
                window.addEventListener('toast', this._onToast);
            },
            destroy() {
                clearTimeout(this._t);
                window.removeEventListener('toast', this._onToast);
            },
        }"
        x-show="visible"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        role="status"
        aria-live="polite"
        class="fixed right-8 top-5 z-[60] flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 shadow-lg"
    >
        <x-icon :name="$entry['name']" class="{{ $entry['class'] }}" />
        <span x-text="text">{{ $text }}</span>
        <button
            type="button"
            class="ml-1 rounded p-1 text-slate-400 transition-colors hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2"
            @click="visible = false"
            aria-label="Cerrar aviso"
        >
            <x-icon name="x-mark" class="h-4 w-4" />
        </button>
    </div>
@endif
