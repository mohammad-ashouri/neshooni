<div class="mx-auto grid min-h-[70vh] max-w-md place-items-center px-4 py-12">
    <div class="card w-full overflow-hidden shadow-xl">
        <div class="bg-linear-to-l from-emerald-600 to-teal-500 px-6 py-5 text-white">
            <div class="text-sm opacity-80">درگاه پرداخت (نسخه نمایشی)</div>
            <div class="mt-1 text-2xl font-black">{{ fa_price($payment->amount) }}</div>
        </div>
        <div class="space-y-3 p-6 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">پذیرنده</span><span class="font-semibold">{{ config('site.name') }}</span></div>
            <div class="flex justify-between gap-4"><span class="text-slate-500">شرح</span><span class="text-left font-semibold">{{ $payment->title }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">شماره سفارش</span><span class="font-mono" dir="ltr">{{ $payment->receiptNumber() }}</span></div>

            @if ($payment->status === 'pending')
                <p class="rounded-xl bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-700">این صفحه جایگزین درگاه بانک است و پولی کسر نمی‌شود.</p>
                <div class="grid grid-cols-2 gap-2 pt-2">
                    <button wire:click="pay" wire:loading.attr="disabled" class="btn-success">پرداخت موفق</button>
                    <button wire:click="cancel" class="btn-light">انصراف</button>
                </div>
            @else
                <p class="rounded-xl bg-slate-100 px-3 py-2 text-center">این پرداخت قبلاً {{ $payment->isPaid() ? 'انجام' : 'لغو' }} شده است.</p>
                <a href="{{ route('dashboard.payments') }}" wire:navigate class="btn-light w-full">پرداخت‌های من</a>
            @endif
        </div>
    </div>
</div>
