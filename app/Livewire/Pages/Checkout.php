<?php

namespace App\Livewire\Pages;

use App\Models\Payment;
use App\Services\PaymentGateways;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * صفحه پرداخت: خلاصه سفارش و انتخاب درگاه.
 * دکمه پرداخت یک فرم معمولی به PaymentController@start است (نه Livewire)،
 * چون پاسخ آن صفحه انتقال به بانک است. درگاه‌های فعال از PAYMENT_GATEWAYS در env می‌آیند.
 */
#[Title('پرداخت')]
class Checkout extends Component
{
    public Payment $payment;

    public function mount(Payment $payment): void
    {
        abort_unless($payment->user_id === Auth::id(), 403);
    }

    public function cancel()
    {
        if ($this->payment->isPending()) {
            $this->payment->update(['status' => 'canceled']);

            // درخواست نشان تأیید بدون پرداخت هم لغو می‌شود
            $this->payment->verificationRequest?->delete();
        }

        session()->flash('toast', ['message' => 'پرداخت لغو شد.', 'type' => 'info']);

        return $this->redirectRoute('dashboard.payments', navigate: true);
    }

    public function render(PaymentGateways $gateways)
    {
        $available = $gateways->available();

        return view('livewire.pages.checkout', [
            'gateways' => $available,
            // آخرین درگاهی که کاربر با آن پرداخت کرده پیش‌فرض انتخاب می‌شود
            'selected' => old('gateway', $this->lastGateway($available)),
        ]);
    }

    /** @param  array<string, string>  $available */
    private function lastGateway(array $available): ?string
    {
        $last = Auth::user()->payments()->whereNotNull('gateway')->latest('id')->value('gateway');

        return isset($available[$last]) ? $last : array_key_first($available);
    }
}
