<?php

namespace App\Livewire\Superadmin;

use App\Enums\BusinessStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreBusinessRequest;
use App\Models\Business;
use App\Models\Order;
use App\Services\BusinessService;
use App\Services\BusinessSuspensionService;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filter = 'todos';

    public bool $showCreateModal = false;

    public bool $showSuspendModal = false;

    public ?int $suspendTargetId = null;

    public string $suspendTargetName = '';

    public bool $suspendConfirmed = false;

    // "Nuevo Negocio" form fields.
    public string $name = '';

    public string $address = '';

    public string $phone = '';

    public string $owner_name = '';

    public string $owner_email = '';

    public string $owner_phone = '';

    public string $password = '';

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return (new StoreBusinessRequest)->rules();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'address', 'phone', 'owner_name', 'owner_email', 'owner_phone', 'password']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function createBusiness(BusinessService $businesses): void
    {
        $data = $this->validate();

        $businesses->createWithOwner($data);

        $this->showCreateModal = false;
        $this->reset(['name', 'address', 'phone', 'owner_name', 'owner_email', 'owner_phone', 'password']);

        session()->flash('success', 'Negocio creado correctamente con su usuario dueño.');
    }

    public function openSuspendModal(int $businessId): void
    {
        $business = Business::findOrFail($businessId);

        $this->suspendTargetId = $business->id;
        $this->suspendTargetName = $business->name;
        $this->suspendConfirmed = false;
        $this->showSuspendModal = true;
    }

    public function closeSuspendModal(): void
    {
        $this->showSuspendModal = false;
        $this->suspendTargetId = null;
        $this->suspendConfirmed = false;
    }

    public function suspend(BusinessSuspensionService $suspension): void
    {
        if (! $this->suspendConfirmed) {
            $this->addError('suspendConfirmed', 'Debes confirmar que entiendes las implicaciones de la ventana de gracia.');

            return;
        }

        $business = Business::findOrFail($this->suspendTargetId);

        $suspension->suspend($business, auth()->user());

        $this->closeSuspendModal();

        session()->flash('success', 'Negocio suspendido. Se activó la ventana de gracia de 24 horas.');
    }

    public function reactivate(int $businessId, BusinessSuspensionService $suspension): void
    {
        $business = Business::findOrFail($businessId);

        $suspension->reactivate($business);

        session()->flash('success', 'Negocio reactivado. El acceso del dueño quedó restablecido.');
    }

    public function closeGrace(int $businessId, BusinessSuspensionService $suspension): void
    {
        $business = Business::findOrFail($businessId);

        $count = $suspension->closeGraceOrders($business, auth()->user());

        session()->flash('success', "Se cerraron {$count} pedido(s) en ventana de gracia.");
    }

    public function render()
    {
        $term = trim($this->search);

        $businesses = Business::query()
            ->withCount('graceOrders')
            ->with(['users' => fn ($query) => $query->where('role', UserRole::Owner->value)])
            ->when($this->filter === 'activos', fn ($query) => $query->where('status', BusinessStatus::Active->value))
            ->when($this->filter === 'suspendidos', fn ($query) => $query->where('status', BusinessStatus::Suspended->value))
            ->when($term !== '', function ($query) use ($term) {
                $like = '%'.$term.'%';
                $query->where(function ($sub) use ($like) {
                    $sub->where('businesses.name', 'like', $like)
                        ->orWhere('businesses.phone', 'like', $like)
                        ->orWhereHas('users', function ($users) use ($like) {
                            $users->where('role', UserRole::Owner->value)
                                ->where(fn ($inner) => $inner->where('users.name', 'like', $like)->orWhere('users.email', 'like', $like));
                        });
                });
            })
            ->orderBy('businesses.name')
            ->paginate(10);

        return view('livewire.superadmin.dashboard', [
            'businesses' => $businesses,
            'totalBusinesses' => Business::count(),
            'activeBusinesses' => Business::where('status', BusinessStatus::Active->value)->count(),
            'suspendedBusinesses' => Business::where('status', BusinessStatus::Suspended->value)->count(),
            'ordersToday' => Order::whereDate('created_at', today())->count(),
            'graceOrders' => Order::whereIn('status', [
                OrderStatus::Assigned->value,
                OrderStatus::InTransit->value,
            ])->count(),
        ]);
    }
}
