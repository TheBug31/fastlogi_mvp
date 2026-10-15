<?php

namespace App\Livewire\Owner;

use App\Support\NotificationPresenter;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Full notification center of the owner panel (RF-002 §4.2.1, RF-008 §4.8).
 * Notifications persist until the owner marks them as read.
 */
class Notifications extends Component
{
    use WithPagination;

    public string $filter = 'todas';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function markAsRead(string $id): void
    {
        $this->findNotification($id)?->markAsRead();
    }

    public function markAsUnread(string $id): void
    {
        $this->findNotification($id)?->update(['read_at' => null]);
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();

        session()->flash('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }

    public function delete(string $id): void
    {
        $this->findNotification($id)?->delete();
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

        $notifications = $user->notifications()
            ->when($this->filter === 'no_leidas', fn ($query) => $query->whereNull('read_at'))
            ->when($this->filter === 'leidas', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(15);

        return view('livewire.owner.notifications', [
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
            'totalCount' => $user->notifications()->count(),
        ]);
    }
}
