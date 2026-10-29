<?php

namespace App\Livewire\Pages;

use App\Models\Payment;
use App\Notifications\SiteMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * درگاه پرداخت نمایشی.
 *
 * TODO: در نسخه واقعی، دکمه پرداخت کاربر را به درگاه بانک (زرین‌پال، آی‌دی‌پی و...) می‌فرستد
 * و متد complete() در مسیر بازگشت (callback) بعد از تأیید تراکنش با درگاه صدا زده می‌شود.
 */
#[Title('پرداخت')]
class Checkout extends Component
{
    public Payment $payment;

    public function mount(Payment $payment): void
    {
        abort_unless($payment->user_id === Auth::id(), 403);
    }

    public function pay()
    {
        abort_unless($this->payment->status === 'pending', 403);

        DB::transaction(function () {
            $this->payment->update([
                'status' => 'paid',
                'ref_id' => (string) random_int(10000000, 99999999),
                'paid_at' => now(),
            ]);

            // آگهی ویژه: روزها به انتهای ویژه بودن فعلی (یا از امروز) اضافه می‌شود
            if ($this->payment->type === 'featured' && $listing = $this->payment->listing) {
                $start = $listing->isFeatured() ? $listing->featured_until : now();
                $listing->update(['featured_until' => $start->copy()->addDays($this->payment->days)]);
            }
        });

        Auth::user()->notify(new SiteMessage('پرداخت «'.$this->payment->title.'» با موفقیت انجام شد.', route('dashboard.payments')));
        session()->flash('toast', ['message' => 'پرداخت موفق بود. کد پیگیری: '.fa_digits($this->payment->ref_id)]);

        return $this->redirectRoute($this->payment->type === 'featured' ? 'dashboard' : 'dashboard.verification', navigate: true);
    }

    public function cancel()
    {
        if ($this->payment->status === 'pending') {
            $this->payment->update(['status' => 'canceled']);

            // درخواست نشان تأیید بدون پرداخت هم لغو می‌شود
            $this->payment->verificationRequest?->delete();
        }

        session()->flash('toast', ['message' => 'پرداخت لغو شد.', 'type' => 'info']);

        return $this->redirectRoute('dashboard.payments', navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.checkout');
    }
}
