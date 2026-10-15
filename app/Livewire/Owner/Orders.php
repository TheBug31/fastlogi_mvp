<?php

namespace App\Livewire\Owner;

use App\Enums\OrderStatus;
use App\Exceptions\BusinessSuspendedException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OfferNotAllowedException;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Models\Product;
use App\Services\DriverAvailabilityService;
use App\Services\OrderService;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'todos';

    // Create modal
    public bool $showCreateModal = false;

    /** @var array<int, array{product_id: int|string, quantity: int|string}> */
    public array $lines = [];

    public string $customer_name = '';

    public string $customer_phone = '';

    public string $delivery_address = '';

    public string $payment_method = 'Contra Entrega (Efectivo)';

    public bool $requires_traceability = false;

    // Offer modal
    public bool $showOfferModal = false;

    public ?int $offerOrderId = null;

    // Cancel modal
    public bool $showCancelModal = false;

    public ?int $cancelOrderId = null;

    public string $cancel_reason = '';

    // Reassign modal
    public bool $showReassignModal = false;

    public ?int $reassignOrderId = null;

    public string $reassign_reason = '';

    // Incident modal
    public bool $showIncidentModal = false;

    public ?int $incidentOrderId = null;

    public string $incident_reason = '';

    // Detail modal
    public bool $showDetailModal = false;

    public ?int $detailOrderId = null;

    public function mount(bool $startCreate = false, ?int $orderId = null): void
    {
        if ($startCreate) {
            $this->openCreateModal();
        }

        if ($orderId !== null) {
            $this->openDetailModal($orderId);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    // ── Create ──────────────────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['customer_name', 'customer_phone', 'delivery_address', 'requires_traceability']);
        $this->lines = [['product_id' => '', 'quantity' => 1]];
        $this->payment_method = 'Contra Entrega (Efectivo)';
        $this->showCreateModal = true;
    }

    public function addLine(): void
    {
        $this->lines[] = ['product_id' => '', 'quantity' => 1];
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);

        if (empty($this->lines)) {
            $this->addLine();
        }
    }

    public function createOrder(OrderService $orders): void
    {
        $this->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'max:100'],
            'requires_traceability' => ['boolean'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
        ], [], [
            'lines.*.product_id' => 'producto',
            'lines.*.quantity' => 'cantidad',
        ]);

        try {
            $orders->createOrder([
                'customer_name' => $this->customer_name,
                'customer_phone' => $this->customer_phone,
                'delivery_address' => $this->delivery_address,
                'payment_method' => $this->payment_method,
                'requires_traceability' => $this->requires_traceability,
                'items' => $this->lines,
            ], auth()->user());
        } catch (InsufficientStockException|BusinessSuspendedException|InvalidOrderTransitionException $e) {
            $this->addError('lines', $e->getMessage());

            return;
        }

        $this->showCreateModal = false;
        session()->flash('success', 'Pedido registrado y stock reservado correctamente.');
    }

    // ── Offer ───────────────────────────────────────────────────────

    public function openOfferModal(int $orderId): void
    {
        $this->resetValidation();
        $this->offerOrderId = $orderId;
        $this->showOfferModal = true;
    }

    public function offerToDriver(int $driverId, OrderService $orders): void
    {
        $order = Order::findOrFail($this->offerOrderId);
        $driver = DeliveryDriver::findOrFail($driverId);

        try {
            $orders->offerToDriver($order, $driver, auth()->user());
        } catch (OfferNotAllowedException|BusinessSuspendedException|InvalidOrderTransitionException $e) {
            $this->addError('offer', $e->getMessage());

            return;
        }

        $this->showOfferModal = false;
        session()->flash('success', 'Oferta enviada; el repartidor tiene 90 segundos para responder.');
    }

    // ── Cancel ──────────────────────────────────────────────────────

    public function openCancelModal(int $orderId): void
    {
        $this->resetValidation();
        $this->cancelOrderId = $orderId;
        $this->cancel_reason = '';
        $this->showCancelModal = true;
    }

    public function cancelOrder(OrderService $orders): void
    {
        $order = Order::findOrFail($this->cancelOrderId);

        try {
            $orders->cancelOrder($order, auth()->user(), $this->cancel_reason ?: null);
        } catch (InvalidOrderTransitionException $e) {
            $this->addError('cancel_reason', $e->getMessage());

            return;
        }

        $this->showCancelModal = false;
        session()->flash('success', 'Pedido cancelado y reserva liberada.');
    }

    // ── Reassign ────────────────────────────────────────────────────

    public function openReassignModal(int $orderId): void
    {
        $this->resetValidation();
        $this->reassignOrderId = $orderId;
        $this->reassign_reason = '';
        $this->showReassignModal = true;
    }

    public function reassign(OrderService $orders): void
    {
        $this->validate(['reassign_reason' => ['required', 'string', 'min:3']], [], [
            'reassign_reason' => 'motivo',
        ]);

        $order = Order::findOrFail($this->reassignOrderId);

        try {
            $orders->reassignToPending($order, auth()->user(), $this->reassign_reason);
        } catch (InvalidOrderTransitionException $e) {
            $this->addError('reassign_reason', $e->getMessage());

            return;
        }

        $this->showReassignModal = false;
        session()->flash('success', 'Pedido devuelto a pendiente para reasignación.');
    }

    // ── Incident / devolution ───────────────────────────────────────

    public function openIncidentModal(int $orderId): void
    {
        $this->resetValidation();
        $this->incidentOrderId = $orderId;
        $this->incident_reason = '';
        $this->showIncidentModal = true;
    }

    public function registerIncident(OrderService $orders): void
    {
        $this->validate(['incident_reason' => ['required', 'string', 'min:3']], [], [
            'incident_reason' => 'motivo',
        ]);

        $order = Order::findOrFail($this->incidentOrderId);

        try {
            $orders->registerIncident($order, auth()->user(), $this->incident_reason);
        } catch (InvalidOrderTransitionException $e) {
            $this->addError('incident_reason', $e->getMessage());

            return;
        }

        $this->showIncidentModal = false;
        session()->flash('success', 'Incidencia registrada. Confirma la devolución para reintegrar el stock.');
    }

    public function confirmDevolution(int $orderId, OrderService $orders): void
    {
        $order = Order::findOrFail($orderId);

        try {
            $orders->confirmDevolution($order, auth()->user());
        } catch (InvalidOrderTransitionException $e) {
            $this->addError('devolution', $e->getMessage());

            return;
        }

        session()->flash('success', 'Devolución confirmada; el stock fue reintegrado.');
    }

    // ── Detail ──────────────────────────────────────────────────────

    public function openDetailModal(int $orderId): void
    {
        $this->detailOrderId = $orderId;
        $this->showDetailModal = true;
    }

    public function render()
    {
        $term = trim($this->search);

        $orders = Order::query()
            ->with(['deliveryDriver.user'])
            ->when($term !== '', function ($query) use ($term) {
                $like = '%'.$term.'%';
                $query->where(fn ($sub) => $sub->where('customer_name', 'like', $like)
                    ->orWhere('id', 'like', $like)
                    ->orWhere('customer_phone', 'like', $like));
            })
            ->when($this->statusFilter !== 'todos', fn ($query) => $query
                ->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        $activeStatuses = [
            OrderStatus::Pending->value,
            OrderStatus::Offered->value,
            OrderStatus::Assigned->value,
            OrderStatus::InTransit->value,
        ];

        $kpis = [
            'activos' => Order::whereIn('status', $activeStatuses)->count(),
            'en_camino' => Order::where('status', OrderStatus::InTransit->value)->count(),
            'entregados' => Order::where('status', OrderStatus::Delivered->value)->count(),
            'pendientes' => Order::where('status', OrderStatus::Pending->value)->count(),
        ];

        $detail = null;

        if ($this->showDetailModal && $this->detailOrderId) {
            $detail = Order::with(['items.product', 'statusLogs.user', 'package.scans', 'deliveryDriver.user'])
                ->find($this->detailOrderId);
        }

        $candidates = $this->showOfferModal
            ? app(DriverAvailabilityService::class)->candidates(auth()->user()->business_id)
            : collect();

        return view('livewire.owner.orders', [
            'orders' => $orders,
            'kpis' => $kpis,
            'products' => Product::orderBy('name')->get(),
            'detail' => $detail,
            'candidates' => $candidates,
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
