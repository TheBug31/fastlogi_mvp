<div class="space-y-6">
    <x-ui.toast />

    <x-ui.page-header title="Centro de Notificaciones"
                      :description="$totalCount . ' en total · ' . $unreadCount . ' sin leer'">
        <x-slot:actions>
            @if ($unreadCount > 0)
                <x-ui.button variant="secondary" wire:click="markAllAsRead">
                    <x-icon name="check" />
                    Marcar todas como leídas
                </x-ui.button>
            @else
                <x-ui.button variant="secondary" disabled>
                    <x-icon name="check" />
                    Marcar todas como leídas
                </x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.tabs>
        <x-ui.tab :active="$filter === 'todas'" :count="$totalCount"
                  wire:click="$set('filter', 'todas')">Todas</x-ui.tab>
        <x-ui.tab :active="$filter === 'no_leidas'" :count="$unreadCount"
                  wire:click="$set('filter', 'no_leidas')">No leídas</x-ui.tab>
        <x-ui.tab :active="$filter === 'leidas'" :count="$totalCount - $unreadCount"
                  wire:click="$set('filter', 'leidas')">Leídas</x-ui.tab>
    </x-ui.tabs>

    @if ($notifications->isEmpty())
        <x-ui.empty-state icon="bell" title="No hay notificaciones para mostrar."
                          description="Aquí verás los avisos de tus pedidos, inventario y repartidores." />
    @else
        <x-ui.card padding="none">
            <div class="divide-y divide-slate-100">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $unread = ! $notification->read_at;
                    @endphp
                    <div wire:key="notif-{{ $notification->id }}"
                         class="flex items-start gap-4 px-6 py-4 {{ $unread ? 'bg-teal-50/40' : '' }}">
                        <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white ring-1 ring-slate-200" aria-hidden="true">
                            <x-icon :name="\App\Support\NotificationPresenter::icon($data)"
                                    class="{{ \App\Support\NotificationPresenter::tone($data) }}" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm {{ $unread ? 'font-semibold text-slate-900' : 'text-slate-700' }}">
                                {{ \App\Support\NotificationPresenter::message($data) }}
                            </p>

                            <div class="mt-1.5 flex flex-wrap items-center gap-3">
                                <span class="text-xs text-slate-500">{{ format_datetime($notification->created_at) }}</span>
                                <span class="text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>

                                @if ($unread)
                                    <x-ui.badge color="teal">Sin leer</x-ui.badge>
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            @if (\App\Support\NotificationPresenter::url($data))
                                <x-ui.button variant="ghost" size="sm" class="w-8 px-0 hover:no-underline"
                                             wire:click="openNotification('{{ $notification->id }}')"
                                             aria-label="Ver notificación" title="Ver">
                                    <x-icon name="eye" class="h-4 w-4" />
                                </x-ui.button>
                            @endif

                            @if ($unread)
                                <x-ui.button variant="ghost" size="sm" class="w-8 px-0 text-slate-500 hover:text-slate-700 hover:no-underline"
                                             wire:click="markAsRead('{{ $notification->id }}')"
                                             aria-label="Marcar como leída" title="Marcar como leída">
                                    <x-icon name="envelope-open" class="h-4 w-4" />
                                </x-ui.button>
                            @else
                                <x-ui.button variant="ghost" size="sm" class="w-8 px-0 text-slate-500 hover:text-slate-700 hover:no-underline"
                                             wire:click="markAsUnread('{{ $notification->id }}')"
                                             aria-label="Marcar como no leída" title="Marcar como no leída">
                                    <x-icon name="envelope" class="h-4 w-4" />
                                </x-ui.button>
                            @endif

                            <x-ui.button variant="ghost" size="sm" class="w-8 px-0 text-red-600 hover:text-red-700 hover:no-underline"
                                         wire:click="delete('{{ $notification->id }}')"
                                         aria-label="Eliminar notificación" title="Eliminar">
                                <x-icon name="trash" class="h-4 w-4" />
                            </x-ui.button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $notifications->links('partials.pagination', ['label' => 'notificaciones']) }}
        </x-ui.card>
    @endif
</div>
