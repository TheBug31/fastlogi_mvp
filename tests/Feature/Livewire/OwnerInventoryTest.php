<?php

use App\Enums\OrderStatus;
use App\Livewire\Owner\Inventory;
use App\Models\Business;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->owner = User::factory()->owner($this->business)->create();
});

test('the inventory lists only the products of the authenticated business', function () {
    Product::factory()->create(['business_id' => $this->business->id, 'name' => 'Producto Propio']);
    $other = Business::factory()->create();
    Product::factory()->create(['business_id' => $other->id, 'name' => 'Producto Ajeno']);

    Livewire::actingAs($this->owner)
        ->test(Inventory::class)
        ->assertSee('Producto Propio')
        ->assertDontSee('Producto Ajeno');
});

test('an owner can create a product', function () {
    Livewire::actingAs($this->owner)
        ->test(Inventory::class)
        ->call('openCreateModal')
        ->set('name', 'Panela')
        ->set('barcode', '7701234567890')
        ->set('price', '3500')
        ->set('stock', '40')
        ->set('min_stock', '10')
        ->set('unit', 'unidad')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('products', [
        'business_id' => $this->business->id,
        'name' => 'Panela',
        'stock' => 40,
    ]);
});

test('a negative stock value is rejected', function () {
    Livewire::actingAs($this->owner)
        ->test(Inventory::class)
        ->call('openCreateModal')
        ->set('name', 'Producto Malo')
        ->set('price', '1000')
        ->set('stock', '-2')
        ->set('min_stock', '5')
        ->call('save')
        ->assertHasErrors(['stock' => 'min']);

    $this->assertDatabaseMissing('products', ['name' => 'Producto Malo']);
});

test('editing the stock down across the minimum fires the alert', function () {
    Notification::fake();

    $product = Product::factory()->create([
        'business_id' => $this->business->id,
        'stock' => 8,
        'min_stock' => 5,
    ]);

    Livewire::actingAs($this->owner)
        ->test(Inventory::class)
        ->call('openEditModal', $product->id)
        ->assertSet('name', $product->name)
        ->set('stock', '2')
        ->call('save')
        ->assertHasNoErrors();

    expect($product->fresh()->stock)->toBe(2)
        ->and($product->fresh()->low_stock_alerted)->toBeTrue();

    Notification::assertSentTo($this->owner, LowStockAlert::class);
});

test('an owner can delete an unused product', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id]);

    Livewire::actingAs($this->owner)
        ->test(Inventory::class)
        ->call('delete', $product->id);

    expect(Product::whereKey($product->id)->exists())->toBeFalse();
});

test('deleting a product with orders is refused with an error message', function () {
    $product = Product::factory()->create(['business_id' => $this->business->id, 'stock' => 10]);
    makeReservedOrder($this->business, $product, OrderStatus::Pending, 1);

    Livewire::actingAs($this->owner)
        ->test(Inventory::class)
        ->call('delete', $product->id)
        ->assertHasErrors('delete');

    expect(Product::whereKey($product->id)->exists())->toBeTrue();
});

test('the search filters products by name', function () {
    Product::factory()->create(['business_id' => $this->business->id, 'name' => 'Café Molido']);
    Product::factory()->create(['business_id' => $this->business->id, 'name' => 'Azúcar']);

    Livewire::actingAs($this->owner)
        ->test(Inventory::class)
        ->set('search', 'Café')
        ->assertSee('Café Molido')
        ->assertDontSee('Azúcar');
});

test('the low stock banner counts the products below their minimum', function () {
    Product::factory()->create(['business_id' => $this->business->id, 'stock' => 2, 'min_stock' => 10]);
    Product::factory()->create(['business_id' => $this->business->id, 'stock' => 50, 'min_stock' => 10]);

    Livewire::actingAs($this->owner)
        ->test(Inventory::class)
        ->assertSee('Atención de inventario')
        ->assertSee('1 producto(s) por debajo');
});
