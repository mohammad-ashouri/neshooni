<?php

namespace App\Livewire\Dashboard;

use App\Models\Category;
use App\Services\InterestTracker;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * اعلان‌ها و تنظیم خبرنامه.
 * کاربر می‌تواند چند دسته (در هر سطح) را برای خبرنامه انتخاب کند؛
 * اشتراک یک دسته شامل همه زیردسته‌های آن هم می‌شود (SubscriptionNotifier).
 */
#[Title('اعلان و خبرنامه')]
class Alerts extends Component
{
    /** شناسه دسته‌های انتخاب‌شده */
    public array $selected = [];

    /** خبرنامه عمومی (همه پیج‌های جدید) */
    public bool $general = false;

    public function mount(): void
    {
        $subscriptions = Auth::user()->subscriptions()->pluck('category_id');
        $this->general = $subscriptions->contains(null);
        $this->selected = $subscriptions->filter()->map(fn ($id) => (string) $id)->values()->all();
    }

    public function save(): void
    {
        $ids = collect($this->selected)->map(fn ($id) => (int) $id)->filter(fn ($id) => Category::fromCache($id))->values();
        if ($this->general) {
            $ids->push(null);
        }

        $user = Auth::user();
        $user->subscriptions()->delete();
        $user->subscriptions()->createMany($ids->map(fn ($id) => ['category_id' => $id])->all());

        $this->dispatch('toast', message: $ids->isEmpty() ? 'از همه خبرنامه‌ها خارج شدید.' : 'تنظیمات خبرنامه ذخیره شد.');
    }

    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function render(InterestTracker $tracker)
    {
        return view('livewire.dashboard.alerts', [
            'roots' => Category::roots(),
            'notifications' => Auth::user()->notifications()->latest()->limit(30)->get(),
            'interests' => $tracker->top(),
        ]);
    }
}
