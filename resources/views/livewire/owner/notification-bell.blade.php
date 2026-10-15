<div class="relative" wire:poll.10s>
    <button
        type="button"
        wire:click="toggle"
        class="relative p-space-sm text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors rounded-lg"
        aria-label="Notificaciones"
    >
        <span class="material-symbols-outlined text-[22px]">notifications</span>
        @if ($unreadCount > 0)
            <span class="absolute top-1 right-1 min-w-4 h-4 px-1 bg-error text-on-error font-label-sm text-label-sm rounded-full flex items-center justify-center leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    @if ($open)
        <div class="fixed inset-0 z-40" wire:click="toggle"></div>

        <div class="absolute right-0 mt-2 w-96 max-w-[90vw] bg-surface-container-lowest border border-outline-variant rounded-lg shadow-xl overflow-hidden flex flex-col z-50">
            <div class="px-space-md py-space-sm bg-surface-container-low border-b border-outline-variant flex items-center justify-between">
                <div class="flex items-center gap-space-xs">
                    @if ($unreadCount > 0)
                        <span class="w-2 h-2 rounded-full bg-error animate-ping"></span>
                    @endif
                    <span class="font-label-lg text-label-lg font-semibold text-on-surface">
                        Notificaciones ({{ $unreadCount }} no leídas)
                    </span>
                </div>
                @if ($unreadCount > 0)
                    <button type="button" wire:click="markAllAsRead"
                            class="font-label-sm text-label-sm text-primary hover:text-on-primary-fixed-variant transition-colors underline decoration-primary/40 underline-offset-2">
                        Marcar todas
                    </button>
                @endif
            </div>

            <div class="max-h-96 overflow-y-auto divide-y divide-outline-variant">
                @forelse ($notifications as $notification)
                    @php $data = $notification->data; @endphp
                    <div wire:key="bell-{{ $notification->id }}"
                         class="flex items-start gap-space-sm px-space-md py-space-sm {{ $notification->read_at ? 'bg-surface-container-lowest' : 'bg-primary-container/5' }}">
                        <span class="material-symbols-outlined text-[20px] mt-0.5 {{ \App\Support\NotificationPresenter::tone($data) }}">
                            {{ \App\Support\NotificationPresenter::icon($data) }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-body-sm text-body-sm text-on-surface {{ $notification->read_at ? '' : 'font-medium' }}">
                                {{ \App\Support\NotificationPresenter::message($data) }}
                            </p>
                            <div class="flex items-center gap-space-sm mt-1">
                                <span class="font-label-sm text-label-sm text-on-surface-variant">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                                @if (! $notification->read_at)
                                    <button type="button" wire:click="markAsRead('{{ $notification->id }}')"
                                            class="font-label-sm text-label-sm text-primary hover:underline">
                                        Marcar leída
                                    </button>
                                @endif
                                @if (\App\Support\NotificationPresenter::url($data))
                                    <button type="button" wire:click="openNotification('{{ $notification->id }}')"
                                            class="font-label-sm text-label-sm text-primary hover:underline">
                                        Ver
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-space-md py-space-lg text-center font-body-sm text-body-sm text-on-surface-variant">
                        No tienes notificaciones.
                    </div>
                @endforelse
            </div>

            <a href="{{ route('owner.notifications.index') }}" wire:navigate
               class="block text-center px-space-md py-space-sm bg-surface-container-low border-t border-outline-variant font-label-lg text-label-lg text-primary hover:bg-surface-container transition-colors">
                Ver centro de notificaciones
            </a>
        </div>
    @endif
</div>
