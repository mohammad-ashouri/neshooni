<?php

namespace App\Livewire\Pages;

use App\Models\Subscription;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * لغو عضویت خبرنامه از لینک داخل پیام (/unsubscribe/{token}).
 * لغو با زدن دکمه انجام می‌شود، نه با باز شدن لینک؛ چون بعضی برنامه‌ها لینک‌ها را خودکار باز می‌کنند.
 * همه اشتراک‌های همان موبایل یا ایمیل (یا همان کاربر) با هم لغو می‌شوند.
 */
#[Title('لغو عضویت خبرنامه')]
class Unsubscribe extends Component
{
    #[Locked]
    public string $token = '';

    public bool $done = false;

    public function mount(string $token): void
    {
        $this->token = $token;

        $data = Seo::set(new SEOData(title: 'لغو عضویت خبرنامه'));
        $data->robots = 'noindex, nofollow';
    }

    public function unsubscribe(): void
    {
        $this->related()->each->delete();
        $this->done = true;
    }

    /** همه اشتراک‌های صاحب این لینک */
    private function related(): Collection
    {
        $subscription = Subscription::where('token', $this->token)->first();
        if (! $subscription) {
            return new Collection;
        }

        return Subscription::with('category')
            ->when(
                $subscription->user_id,
                fn ($q) => $q->where('user_id', $subscription->user_id),
                fn ($q) => $q->whereNull('user_id')->where('contact', $subscription->contact),
            )
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.unsubscribe', ['subscriptions' => $this->done ? new Collection : $this->related()]);
    }
}
