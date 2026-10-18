@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación"
         class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-white px-4 py-3">
        <p class="text-xs text-slate-500">
            Mostrando
            <span class="font-medium tabular-nums text-slate-700">{{ $paginator->firstItem() }}</span>
            a
            <span class="font-medium tabular-nums text-slate-700">{{ $paginator->lastItem() }}</span>
            de
            <span class="font-medium tabular-nums text-slate-700">{{ $paginator->total() }}</span>
            {{ $label ?? 'pedidos' }}
        </p>

        <div class="flex flex-wrap items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <x-ui.button variant="secondary" size="sm" disabled aria-label="Página anterior">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    Anterior
                </x-ui.button>
            @else
                <x-ui.button variant="secondary" size="sm" aria-label="Página anterior"
                             wire:click="previousPage('{{ $paginator->getPageName() }}')">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                    Anterior
                </x-ui.button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1 text-xs text-slate-400" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ((int) $page === (int) $paginator->currentPage())
                            <x-ui.button variant="primary" size="sm" disabled aria-current="page"
                                         aria-label="Página {{ $page }}">{{ $page }}</x-ui.button>
                        @else
                            <x-ui.button variant="secondary" size="sm" wire:key="pagina-{{ $page }}"
                                         aria-label="Ir a la página {{ $page }}"
                                         wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</x-ui.button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <x-ui.button variant="secondary" size="sm" aria-label="Página siguiente"
                             wire:click="nextPage('{{ $paginator->getPageName() }}')">
                    Siguiente
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </x-ui.button>
            @else
                <x-ui.button variant="secondary" size="sm" disabled aria-label="Página siguiente">
                    Siguiente
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </x-ui.button>
            @endif
        </div>
    </nav>
@endif
