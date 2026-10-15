<?php

namespace App\Livewire\Owner;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductInUseException;
use App\Models\Product;
use App\Services\InventoryService;
use Livewire\Component;
use Livewire\WithPagination;

class Inventory extends Component
{
    use WithPagination;

    public const UNITS = ['unidad', 'kilogramo', 'litro', 'porcion'];

    public string $search = '';

    public string $statusFilter = 'todos';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $barcode = '';

    public string $price = '';

    public string $stock = '0';

    public string $min_stock = '0';

    public string $unit = 'unidad';

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'in:'.implode(',', self::UNITS)],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['name', 'barcode', 'price', 'stock', 'min_stock', 'unit', 'editingId']);
        $this->stock = '0';
        $this->min_stock = '0';
        $this->unit = 'unidad';
        $this->showModal = true;
    }

    public function openEditModal(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->resetValidation();
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->barcode = (string) $product->barcode;
        $this->price = (string) $product->price;
        $this->stock = (string) $product->stock;
        $this->min_stock = (string) $product->min_stock;
        $this->unit = $product->unit;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function save(InventoryService $inventory): void
    {
        $data = $this->validate();

        try {
            if ($this->editingId !== null) {
                $product = Product::findOrFail($this->editingId);
                $inventory->updateProduct($product, $data, auth()->user());
                $message = 'Producto actualizado correctamente.';
            } else {
                $inventory->createProduct($data, auth()->user());
                $message = 'Producto registrado correctamente.';
            }
        } catch (InsufficientStockException $e) {
            $this->addError('stock', $e->getMessage());

            return;
        }

        $this->showModal = false;
        $this->reset(['name', 'barcode', 'price', 'stock', 'min_stock', 'unit', 'editingId']);
        session()->flash('success', $message);
    }

    public function delete(int $productId, InventoryService $inventory): void
    {
        $product = Product::findOrFail($productId);

        try {
            $inventory->deleteProduct($product);
            session()->flash('success', 'Producto eliminado correctamente.');
        } catch (ProductInUseException $e) {
            $this->addError('delete', $e->getMessage());
        }
    }

    public function render()
    {
        $term = trim($this->search);

        $products = Product::query()
            ->when($term !== '', function ($query) use ($term) {
                $like = '%'.$term.'%';
                $query->where(fn ($sub) => $sub->where('name', 'like', $like)
                    ->orWhere('barcode', 'like', $like));
            })
            ->when($this->statusFilter === 'bajo', fn ($query) => $query
                ->where('min_stock', '>', 0)
                ->whereColumn('stock', '<', 'min_stock')
                ->where('stock', '>', 0))
            ->when($this->statusFilter === 'agotado', fn ($query) => $query
                ->where('stock', '<=', 0))
            ->when($this->statusFilter === 'normal', fn ($query) => $query
                ->where(fn ($sub) => $sub->where('min_stock', '<=', 0)
                    ->orWhereColumn('stock', '>=', 'min_stock')))
            ->orderBy('name')
            ->paginate(10);

        $lowStockCount = Product::query()
            ->where('min_stock', '>', 0)
            ->whereColumn('stock', '<', 'min_stock')
            ->count();

        return view('livewire.owner.inventory', [
            'products' => $products,
            'lowStockCount' => $lowStockCount,
            'units' => self::UNITS,
        ]);
    }
}
