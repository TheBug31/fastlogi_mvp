<div class="space-y-6">
    <x-ui.page-header title="Configuración"
                      description="Datos de tu negocio y de tu cuenta de acceso." />

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <x-ui.kpi label="Repartidores" icon="users">{{ $stats['repartidores'] }}</x-ui.kpi>
        <x-ui.kpi label="Productos" icon="archive-box">{{ $stats['productos'] }}</x-ui.kpi>
        <x-ui.kpi label="Pedidos" icon="truck">{{ $stats['pedidos'] }}</x-ui.kpi>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="text-base font-semibold text-slate-900">Datos del negocio</h2>
            <p class="mt-1 text-sm text-slate-500">Aparecen en la cabecera del panel y en los reportes.</p>

            <form wire:submit="updateBusiness" class="mt-5 space-y-5">
                <x-ui.input name="name" label="Nombre del negocio" wire:model="name" required />
                <x-ui.input name="address" label="Dirección" wire:model="address" required />
                <x-ui.input name="phone" label="Teléfono" wire:model="phone" required />

                <div class="flex items-center justify-end">
                    <x-ui.button type="submit">
                        <x-icon name="check" class="h-5 w-5" />
                        Guardar cambios
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-base font-semibold text-slate-900">Mi cuenta</h2>
            <p class="mt-1 text-sm text-slate-500">Con estos datos inicias sesión en la plataforma.</p>

            <form wire:submit="updateAccount" class="mt-5 space-y-5">
                <x-ui.input name="owner_name" label="Nombre del dueño" wire:model="owner_name" required />
                <x-ui.input name="owner_email" type="email" label="Correo electrónico" wire:model="owner_email" required />
                <x-ui.input name="owner_phone" label="Teléfono de contacto" wire:model="owner_phone" required />

                <div class="flex items-center justify-end">
                    <x-ui.button type="submit">
                        <x-icon name="check" class="h-5 w-5" />
                        Guardar cambios
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

    <x-ui.card>
        <h2 class="text-base font-semibold text-slate-900">Cambiar contraseña</h2>
        <p class="mt-1 text-sm text-slate-500">Usa una contraseña de al menos 8 caracteres.</p>

        <form wire:submit="updatePassword" class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
            <x-ui.input name="current_password" type="password" label="Contraseña actual" wire:model="current_password" autocomplete="current-password" required />
            <x-ui.input name="password" type="password" label="Nueva contraseña" wire:model="password" autocomplete="new-password" required />
            <x-ui.input name="password_confirmation" type="password" label="Confirmar nueva contraseña" wire:model="password_confirmation" autocomplete="new-password" required />

            <div class="flex items-center justify-end sm:col-span-3">
                <x-ui.button type="submit">
                    <x-icon name="lock-closed" class="h-5 w-5" />
                    Actualizar contraseña
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
