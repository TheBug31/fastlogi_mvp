<?php

use App\Livewire\Owner\NotificationBell;
use App\Livewire\Owner\Notifications as NotificationCenter;
use App\Models\Business;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->owner = User::factory()->owner($this->business)->create();
});

if (! function_exists('notifFor')) {
    function notifFor(User $user, array $data, bool $read = false)
    {
        return $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\OrderCancelled',
            'data' => $data,
            'read_at' => $read ? now() : null,
        ]);
    }
}

test('the bell counts only the unread notifications of the authenticated user', function () {
    notifFor($this->owner, ['type' => 'order_cancelled', 'message' => 'Pedido cancelado de prueba']);
    notifFor($this->owner, ['type' => 'order_cancelled', 'message' => 'Ya leída'], read: true);

    $other = User::factory()->owner($this->business)->create();
    notifFor($other, ['type' => 'order_cancelled', 'message' => 'De otro usuario']);

    Livewire::actingAs($this->owner)
        ->test(NotificationBell::class)
        ->assertViewHas('unreadCount', 1);
});

test('the bell dropdown lists the recent notifications of the user', function () {
    notifFor($this->owner, ['type' => 'low_stock', 'message' => 'Aviso de stock bajo']);

    Livewire::actingAs($this->owner)
        ->test(NotificationBell::class)
        ->call('toggle')
        ->assertSee('Aviso de stock bajo')
        ->call('markAllAsRead')
        ->assertViewHas('unreadCount', 0);
});

test('a notification can be marked as read from the bell', function () {
    $notification = notifFor($this->owner, ['type' => 'order_cancelled', 'message' => 'Uno']);

    Livewire::actingAs($this->owner)
        ->test(NotificationBell::class)
        ->call('markAsRead', $notification->id);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('the notification center only lists the notifications of the authenticated user', function () {
    notifFor($this->owner, ['type' => 'order_cancelled', 'message' => 'Mi notificación visible']);

    $other = User::factory()->owner(Business::factory()->create())->create();
    notifFor($other, ['type' => 'order_cancelled', 'message' => 'Notificación de otro negocio']);

    Livewire::actingAs($this->owner)
        ->test(NotificationCenter::class)
        ->assertSee('Mi notificación visible')
        ->assertDontSee('Notificación de otro negocio');
});

test('the notification center filters unread and read notifications', function () {
    notifFor($this->owner, ['type' => 'order_cancelled', 'message' => 'Pendiente por leer']);
    notifFor($this->owner, ['type' => 'order_cancelled', 'message' => 'Ya leída antes'], read: true);

    Livewire::actingAs($this->owner)
        ->test(NotificationCenter::class)
        ->set('filter', 'no_leidas')
        ->assertSee('Pendiente por leer')
        ->assertDontSee('Ya leída antes')
        ->set('filter', 'leidas')
        ->assertSee('Ya leída antes')
        ->assertDontSee('Pendiente por leer');
});

test('opening an order notification redirects to the order detail and marks it read', function () {
    $order = Order::factory()->create(['business_id' => $this->business->id]);
    $notification = notifFor($this->owner, [
        'type' => 'order_cancelled',
        'order_id' => $order->id,
        'message' => 'Pedido cancelado',
    ]);

    Livewire::actingAs($this->owner)
        ->test(NotificationCenter::class)
        ->call('openNotification', $notification->id)
        ->assertRedirect(route('owner.orders.show', $order));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('the notification center can mark all as read and delete notifications', function () {
    notifFor($this->owner, ['type' => 'order_cancelled', 'message' => 'A']);
    $toDelete = notifFor($this->owner, ['type' => 'order_cancelled', 'message' => 'B']);

    Livewire::actingAs($this->owner)
        ->test(NotificationCenter::class)
        ->call('markAllAsRead')
        ->assertViewHas('unreadCount', 0)
        ->call('delete', $toDelete->id);

    expect($this->owner->notifications()->whereKey($toDelete->id)->exists())->toBeFalse();
});
