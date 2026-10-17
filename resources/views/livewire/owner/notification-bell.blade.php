<div wire:poll.10s>
    <button
        type="button"
        wire:click="toggle"
        aria-label="Notificaciones"
        class="relative flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2"
    >
        <x-icon name="bell" />

        @if ($unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-semibold leading-none text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    @if ($open)
        <x-ui.drawer title="Notificaciones" property="open" size="sm">
            <div x-data="{ tab: 'todas' }" class="space-y-3">
                <x-ui.tabs>
                    <x-ui.tab x-active="tab === 'todas'" :count="$notifications->count()" @click="tab = 'todas'">Todas</x-ui.tab>
                    <x-ui.tab x-active="tab === 'no_leidas'" :count="$unreadCount" @click="tab = 'no_leidas'">No leídas</x-ui.tab>
                </x-ui.tabs>

                @forelse ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $unread = ! $notification->read_at;
                        $show = $unread ? "tab === 'todas' || tab === 'no_leidas'" : "tab === 'todas'";
                    @endphp

                    <div
                        wire:key="bell-{{ $notification->id }}"
                        x-show="{{ $show }}"
                        class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4"
                    >
                        <x-icon :name="\App\Support\NotificationPresenter::icon($data)" class="mt-0.5 shrink-0 {{ \App\Support\NotificationPresenter::tone($data) }}" />

                        <div class="min-w-0 flex-1">
                            <p class="text-sm {{ $unread ? 'font-medium text-slate-900' : 'text-slate-600' }}">
                                {{ \App\Support\NotificationPresenter::message($data) }}
                            </p>

                            <div class="mt-1.5 flex flex-wrap items-center gap-3">
                                <span class="text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>

                                @if ($unread)
                                    <button type="button" wire:click="markAsRead('{{ $notification->id }}')"
                                            class="text-xs font-medium text-teal-700 hover:underline">
                                        Marcar leída
                                    </button>
                                @endif

                                @if (\App\Support\NotificationPresenter::url($data))
                                    <button type="button" wire:click="openNotification('{{ $notification->id }}')"
                                            class="text-xs font-medium text-teal-700 hover:underline">
                                        Ver
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state icon="bell" title="No tienes notificaciones." description="Aquí verás los avisos de tus pedidos, inventario y repartidores." />
                @endforelse

                <a href="{{ route('owner.notifications.index') }}" wire:navigate
                   class="block pt-1 text-center text-sm font-medium text-teal-700 hover:underline">
                    Ver centro de notificaciones
                </a>
            </div>

            <x-slot:footer>
                @if ($unreadCount > 0)
                    <x-ui.button variant="ghost" wire:click="markAllAsRead">Marcar todas como leídas</x-ui.button>
                @endif

                <x-ui.button variant="secondary" @click="$wire.set('open', false)">Cerrar</x-ui.button>
            </x-slot:footer>
        </x-ui.drawer>
    @endif
</div>
