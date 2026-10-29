<?php

namespace App\Livewire\Dashboard;

use App\Enums\ModerationStatus;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * خرید آگهی ویژه برای یک پیج.
 * یک پرداخت در انتظار ساخته می‌شود و کاربر به درگاه (فعلاً نمایشی) می‌رود.
 * بعد از پرداخت موفق، روزها به انتهای ویژه بودن فعلی اضافه می‌شود (Pages\Checkout).
 */
#[Title('آگهی ویژه')]
class Feature extends Component
{
    public Listing $listing;

    public function mount(Listing $listing): void
    {
        abort_unless($listing->user_id === Auth::id(), 403);
        abort_unless($listing->status === ModerationStatus::Approved, 403, 'فقط پیج تأییدشده را می‌توان ویژه کرد.');
    }

    public function buy(int $days)
    {
        $plan = collect(config('site.featured_plans'))->firstWhere('days', $days) ?? abort(404);

        $payment = Auth::user()->payments()->create([
            'type' => 'featured',
            'listing_id' => $this->listing->id,
            'days' => $plan['days'],
            'amount' => $plan['price'],
            'title' => "آگهی ویژه {$plan['label']} — {$this->listing->title}",
            'status' => 'pending',
        ]);

        return $this->redirectRoute('checkout', $payment, navigate: true);
    }

    public function render()
    {
        return view('livewire.dashboard.feature', ['plans' => config('site.featured_plans')]);
    }
}
