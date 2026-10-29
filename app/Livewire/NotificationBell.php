<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * زنگوله اعلان‌ها در هدر (اعلان‌های دیتابیسی Laravel).
 */
class NotificationBell extends Component
{
    /** با کلیک روی اعلان، خوانده‌شده می‌شود و کاربر به لینک آن می‌رود */
    public function open(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->redirect($notification->data['url'] ?? route('dashboard'), navigate: true);
    }

    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        return view('livewire.notification-bell', [
            'notifications' => Auth::user()->notifications()->latest()->limit(8)->get(),
            'unread' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
