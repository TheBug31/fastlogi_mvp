<?php

namespace App\Livewire\Owner;

use App\Support\NotificationPresenter;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

/**
 * Header bell of the owner panel. It polls every 10 seconds (RF-002 §4.2.1, the
 * same interval used for in-app notifications) and exposes the persisted
 * notification center: unread counter, recent list and mark-as-read actions.
 */
class NotificationBell extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markAsRead(string $id): void
    {
        $this->findNotification($id)?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function openNotification(string $id): void
    {
        $notification = $this->findNotification($id);

        if (! $notification) {
            return;
        }

        $notification->markAsRead();

        $url = NotificationPresenter::url($notification->data);

        if ($url) {
            $this->redirect($url, navigate: true);
        }
    }

    private function findNotification(string $id): ?DatabaseNotification
    {
        return auth()->user()->notifications()->whereKey($id)->first();
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.owner.notification-bell', [
            'unreadCount' => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()->latest()->take(8)->get(),
        ]);
    }
}
