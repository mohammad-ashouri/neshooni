<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Subscription;
use App\Services\InterestTracker;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * مودال پیشنهاد خبرنامه.
 * - اگر کاربر به دسته‌ای علاقه نشان داده باشد (InterestTracker)، خبرنامه همان دسته پیشنهاد می‌شود.
 * - مهمانی که چند صفحه دیده ولی علاقه مشخصی ندارد، خبرنامه عمومی می‌بیند.
 * - در هر session حداکثر یک بار و فقط در صفحه‌های گشت‌وگذار نشان داده می‌شود.
 */
class NewsletterModal extends Component
{
    public ?int $categoryId = null;

    public bool $visible = false;

    public bool $done = false;

    /** موبایل یا ایمیل مهمان */
    public string $contact = '';

    /** فقط در صفحه‌های گشت‌وگذار (config/site.php → newsletter_routes) */
    #[Locked]
    public bool $enabled = false;

    public function mount(InterestTracker $tracker): void
    {
        $this->enabled = request()->routeIs(config('site.newsletter_routes'));
        $this->check($tracker);
    }

    // بعد از دنبال کردن یا ثبت نظر، کامپوننت صفحه این رویداد را می‌فرستد
    #[On('interest-updated')]
    public function check(InterestTracker $tracker): void
    {
        if ($this->visible || ! $this->enabled || ! $tracker->canOffer()) {
            return;
        }

        $this->categoryId = $tracker->suggestion()?->id;
        $this->visible = $this->categoryId !== null || $tracker->shouldOfferGeneralNewsletter();

        if ($this->visible) {
            $tracker->markOffered();
        }
    }

    public function subscribe(): void
    {
        if (Auth::check()) {
            Subscription::firstOrCreate(['user_id' => Auth::id(), 'category_id' => $this->categoryId]);
        } else {
            $this->contact = en_digits(trim($this->contact));
            $this->validate(
                ['contact' => ['required', 'regex:/^(09\d{9}|[^@\s]+@[^@\s]+\.[^@\s]+)$/']],
                ['contact.regex' => 'یک شماره موبایل (۰۹...) یا ایمیل معتبر وارد کنید.'],
            );
            Subscription::firstOrCreate(['contact' => $this->contact, 'category_id' => $this->categoryId]);

            if ($this->categoryId) {
                session()->push('subscribed_categories', $this->categoryId);
            } else {
                session(['newsletter_subscribed' => true]);
            }
        }

        $this->done = true;
    }

    public function dismiss(InterestTracker $tracker): void
    {
        if (! $this->done) {
            $tracker->dismiss($this->categoryId);
        }
        $this->visible = false;
        $this->done = false;
    }

    public function render()
    {
        return view('livewire.newsletter-modal', [
            'category' => Category::fromCache($this->categoryId),
        ]);
    }
}
