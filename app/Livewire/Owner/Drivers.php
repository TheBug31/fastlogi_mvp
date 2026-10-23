<?php

namespace App\Livewire\Owner;

use App\Enums\DriverAvailability;
use App\Enums\OrderStatus;
use App\Models\DeliveryDriver;
use App\Services\DriverManagementService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Owner fleet management (RF-005 / CU07): onboard drivers, edit their profile,
 * adjust the per-driver capacity limit (1..8) and toggle the manual offline
 * state. The busy/available state is always recomputed from the workload.
 */
class Drivers extends Component
{
    use WithPagination;

    public string $search = '';

    public string $availabilityFilter = 'todos';

    // Create modal
    public bool $showCreateModal = false;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public int $max_active_orders = 3;

    // Edit modal
    public bool $showEditModal = false;

    public ?int $editingId = null;

    public string $edit_name = '';

    public string $edit_email = '';

    public string $edit_phone = '';

    public int $edit_max_active_orders = 3;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAvailabilityFilter(): void
    {
        $this->resetPage();
    }

    // ── Create ──────────────────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'email', 'phone', 'password']);
        $this->max_active_orders = 3;
        $this->showCreateModal = true;
    }

    public function createDriver(DriverManagementService $drivers): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'max_active_orders' => ['required', 'integer', 'min:1', 'max:8'],
        ]);

        $drivers->createDriver(auth()->user()->business, $validated);

        $this->showCreateModal = false;
        $this->reset(['name', 'email', 'phone', 'password']);

        $this->dispatch('toast', message: 'Repartidor vinculado correctamente. Ya puede iniciar sesión con sus credenciales.');
    }

    // ── Edit ────────────────────────────────────────────────────────

    public function openEditModal(int $driverId): void
    {
        $this->resetValidation();

        $driver = DeliveryDriver::with('user')->findOrFail($driverId);

        $this->editingId = $driver->id;
        $this->edit_name = $driver->user->name;
        $this->edit_email = $driver->user->email;
        $this->edit_phone = $driver->user->phone ?? '';
        $this->edit_max_active_orders = $driver->max_active_orders;
        $this->showEditModal = true;
    }

    public function updateDriver(DriverManagementService $drivers): void
    {
        $driver = DeliveryDriver::findOrFail($this->editingId);

        $validated = $this->validate([
            'edit_name' => ['required', 'string', 'max:255'],
            'edit_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($driver->user_id)],
            'edit_phone' => ['required', 'string', 'max:30'],
            'edit_max_active_orders' => ['required', 'integer', 'min:1', 'max:8'],
        ]);

        $drivers->updateDriver($driver, [
            'name' => $validated['edit_name'],
            'email' => $validated['edit_email'],
            'phone' => $validated['edit_phone'],
            'max_active_orders' => $validated['edit_max_active_orders'],
        ]);

        $this->showEditModal = false;
        $this->dispatch('toast', message: 'Datos del repartidor actualizados.');
    }

    // ── Availability ────────────────────────────────────────────────

    public function toggleAvailability(int $driverId, DriverManagementService $drivers): void
    {
        $driver = DeliveryDriver::findOrFail($driverId);

        $target = $driver->availability === DriverAvailability::Offline
            ? DriverAvailability::Available
            : DriverAvailability::Offline;

        $drivers->setAvailability($driver, $target);

        $this->dispatch('toast', message: $target === DriverAvailability::Offline
            ? 'El repartidor quedó marcado como desconectado.'
            : 'El repartidor quedó disponible para recibir ofertas.');
    }

    public function render()
    {
        $term = trim($this->search);

        $drivers = DeliveryDriver::query()
            ->with('user')
            ->withCount(['orders as active_orders_count' => fn ($query) => $query->whereIn('status', [
                OrderStatus::Offered->value,
                OrderStatus::Assigned->value,
                OrderStatus::InTransit->value,
            ])])
            ->when($this->availabilityFilter !== 'todos', fn ($query) => $query
                ->where('availability', $this->availabilityFilter))
            ->when($term !== '', function ($query) use ($term) {
                $like = '%'.$term.'%';
                $query->whereHas('user', fn ($user) => $user
                    ->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like));
            })
            ->orderBy('id')
            ->paginate(10);

        $disconnectedWithOrdersCount = DeliveryDriver::query()
            ->where('availability', DriverAvailability::Offline->value)
            ->whereHas('activeOrders')
            ->count();

        return view('livewire.owner.drivers', [
            'drivers' => $drivers,
            'disconnectedWithOrdersCount' => $disconnectedWithOrdersCount,
            'kpis' => [
                'total' => DeliveryDriver::count(),
                'disponibles' => DeliveryDriver::where('availability', DriverAvailability::Available->value)->count(),
                'ocupados' => DeliveryDriver::where('availability', DriverAvailability::Busy->value)->count(),
                'offline' => DeliveryDriver::where('availability', DriverAvailability::Offline->value)->count(),
            ],
            'availabilities' => DriverAvailability::cases(),
        ]);
    }
}
