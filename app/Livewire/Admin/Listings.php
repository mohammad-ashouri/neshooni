<?php

namespace App\Livewire\Admin;

use App\Enums\ModerationStatus;
use App\Models\Listing;
use App\Notifications\SiteMessage;
use App\Services\SubscriptionNotifier;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * بررسی پیج‌ها: تأیید، رد با دلیل، ویژه کردن دستی و حذف.
 */
#[Layout('layouts::admin'), Title('مدیریت پیج‌ها')]
class Listings extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    #[Url(except: '')]
    public string $q = '';

    /** پیجی که فرم رد آن باز است */
    public ?int $rejecting = null;

    public string $reason = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function approve(int $id, SubscriptionNotifier $notifier): void
    {
        $listing = Listing::findOrFail($id);
        $firstTime = $listing->published_at === null;

        $listing->update(['status' => ModerationStatus::Approved, 'reject_reason' => null, 'published_at' => $listing->published_at ?? now()]);
        $listing->user->notify(new SiteMessage("پیج «{$listing->title}» تأیید و منتشر شد.", route('listings.show', $listing)));

        // فقط در اولین انتشار به مشترکان خبرنامه خبر داده می‌شود
        $notified = $firstTime ? $notifier->notify($listing) : 0;

        $this->dispatch('toast', message: 'پیج تأیید شد'.($notified ? ' و به '.fa_number($notified).' مشترک خبر داده شد.' : '.'));
    }

    public function startReject(int $id): void
    {
        $this->rejecting = $id;
        $this->reason = '';
    }

    public function reject(): void
    {
        $this->validate(['reason' => ['required', 'string', 'max:255']], [], ['reason' => 'دلیل رد']);

        $listing = Listing::findOrFail($this->rejecting);
        $listing->update(['status' => ModerationStatus::Rejected, 'reject_reason' => $this->reason]);
        $listing->user->notify(new SiteMessage("پیج «{$listing->title}» رد شد: {$this->reason}", route('dashboard.listings.edit', $listing)));

        $this->rejecting = null;
        $this->dispatch('toast', message: 'پیج رد شد.', type: 'info');
    }

    /** ویژه کردن دستی (مثلاً هدیه یا پرداخت خارج از سایت)؛ 0 روز = لغو ویژه */
    public function feature(int $id, int $days): void
    {
        Gate::authorize('listings.feature');

        $listing = Listing::findOrFail($id);
        $start = $listing->isFeatured() ? $listing->featured_until : now();
        $listing->update(['featured_until' => $days ? $start->copy()->addDays($days) : null]);

        $this->dispatch('toast', message: $days ? 'پیج '.fa_number($days).' روز ویژه شد.' : 'ویژه بودن لغو شد.');
    }

    public function delete(int $id): void
    {
        Gate::authorize('listings.delete');

        $listing = Listing::with('photos')->findOrFail($id);
        Storage::disk('public')->delete(array_filter([$listing->image, ...$listing->photos->pluck('path')]));
        $listing->delete();

        $this->dispatch('toast', message: 'پیج حذف شد.');
    }

    public function render()
    {
        return view('livewire.admin.listings', [
            'listings' => Listing::with(['platform', 'category', 'user'])
                ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
                ->when($this->q !== '', fn ($q) => $q->search($this->q))
                ->latest()
                ->paginate(15),
            'counts' => Listing::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}
