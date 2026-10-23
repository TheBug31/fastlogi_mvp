<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button
        type="button"
        @click="open = ! open"
        :aria-expanded="open ? 'true' : 'false'"
        aria-haspopup="true"
        title="Cambiar mi disponibilidad"
        class="inline-flex items-center gap-1 rounded-full transition-opacity hover:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2"
    >
        <x-ui.availability-badge :availability="$availability" />
        <x-icon name="chevron-down" class="h-4 w-4 text-slate-400" />
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.origin.top.right
        class="absolute right-0 z-40 mt-2 w-60 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
        wire:key="availability-menu"
    >
        <p class="border-b border-slate-100 px-4 py-2.5 text-xs font-medium text-slate-500">Mi disponibilidad</p>

        @foreach ($options as $option)
            <button
                type="button"
                wire:click="setAvailability('{{ $option['value'] }}')"
                @click="open = false"
                class="flex w-full items-start gap-3 px-4 py-2.5 text-left transition-colors hover:bg-slate-50"
            >
                <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $option['dot'] }}" aria-hidden="true"></span>
                <span class="min-w-0">
                    <span class="flex items-center gap-2 text-sm font-medium text-slate-900">
                        {{ $option['label'] }}
                        @if ($option['current'])
                            <x-icon name="check" class="h-4 w-4 text-teal-600" />
                        @endif
                    </span>
                    <span class="block text-xs text-slate-500">{{ $option['hint'] }}</span>
                </span>
            </button>
        @endforeach

        @unless ($isOffline)
            <p class="border-t border-slate-100 px-4 py-2.5 text-xs text-slate-500">
                Si te desconectas dejas de recibir ofertas.
            </p>
        @endunless
    </div>
</div>
