<div class="flex flex-col w-full pb-16">
    @if (session('success'))
        <div class="w-full bg-primary-fixed text-on-primary-fixed px-space-lg py-space-sm flex items-center gap-space-sm mb-space-md">
            <span class="material-symbols-outlined text-[20px]">check_circle</span>
            <span class="font-body-md">{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md mb-space-lg">
        <div>
            <h1 class="font-headline-xl text-headline-xl text-on-surface">Centro de Notificaciones</h1>
            <p class="font-body-md text-on-surface-variant">
                {{ $totalCount }} en total · {{ $unreadCount }} sin leer
            </p>
        </div>
        <div class="flex items-center gap-space-sm">
            <button type="button" wire:click="markAllAsRead"
                    class="px-space-md py-space-sm border border-outline-variant font-label-lg rounded-lg hover:bg-surface-container transition-colors flex items-center gap-space-xs">
                <span class="material-symbols-outlined text-[18px]">done_all</span>
                Marcar todas como leídas
            </button>
        </div>
    </div>

    <div class="flex items-center gap-space-xs mb-space-md">
        @foreach (['todas' => 'Todas', 'no_leidas' => 'No leídas', 'leidas' => 'Leídas'] as $value => $label)
            <button type="button" wire:click="$set('filter', '{{ $value }}')"
                    class="px-space-md py-space-xs rounded-full font-label-lg transition-colors {{ $filter === $value ? 'bg-primary text-on-primary' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="bg-surface-container-lowest border border-outline-variant divide-y divide-outline-variant">
        @forelse ($notifications as $notification)
            @php $data = $notification->data; @endphp
            <div wire:key="notif-{{ $notification->id }}"
                 class="flex items-start gap-space-md px-space-lg py-space-md {{ $notification->read_at ? '' : 'bg-primary-container/5' }}">
                <span class="material-symbols-outlined text-[24px] mt-0.5 {{ \App\Support\NotificationPresenter::tone($data) }}">
                    {{ \App\Support\NotificationPresenter::icon($data) }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="font-body-md text-on-surface {{ $notification->read_at ? '' : 'font-medium' }}">
                        {{ \App\Support\NotificationPresenter::message($data) }}
                    </p>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">
                        {{ $notification->created_at->format('d/m/Y H:i') }} · {{ $notification->created_at->diffForHumans() }}
                        @if (! $notification->read_at) · <span class="text-primary">Sin leer</span> @endif
                    </span>
                </div>
                <div class="flex items-center gap-space-xs shrink-0">
                    @if (\App\Support\NotificationPresenter::url($data))
                        <button type="button" wire:click="openNotification('{{ $notification->id }}')"
                                class="p-1.5 text-primary hover:bg-surface-container rounded-lg" title="Ver">
                            <span class="material-symbols-outlined text-[20px]">open_in_new</span>
                        </button>
                    @endif
                    @if ($notification->read_at)
                        <button type="button" wire:click="markAsUnread('{{ $notification->id }}')"
                                class="p-1.5 text-on-surface-variant hover:bg-surface-container rounded-lg" title="Marcar como no leída">
                            <span class="material-symbols-outlined text-[20px]">mark_email_unread</span>
                        </button>
                    @else
                        <button type="button" wire:click="markAsRead('{{ $notification->id }}')"
                                class="p-1.5 text-on-surface-variant hover:bg-surface-container rounded-lg" title="Marcar como leída">
                            <span class="material-symbols-outlined text-[20px]">mark_email_read</span>
                        </button>
                    @endif
                    <button type="button" wire:click="delete('{{ $notification->id }}')"
                            class="p-1.5 text-on-surface-variant hover:text-error hover:bg-surface-container rounded-lg" title="Eliminar">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="px-space-lg py-space-xl text-center font-body-md text-on-surface-variant">
                No hay notificaciones para mostrar.
            </div>
        @endforelse
    </div>

    <div class="mt-space-md">
        {{ $notifications->links() }}
    </div>
</div>
