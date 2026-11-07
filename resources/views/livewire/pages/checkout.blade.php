<div class="mx-auto grid min-h-[70vh] max-w-md place-items-center px-4 py-12">
    <div class="card w-full overflow-hidden shadow-xl">
        <div class="bg-linear-to-l from-emerald-600 to-teal-500 px-6 py-5 text-white">
            <div class="text-sm opacity-80">مبلغ قابل پرداخت</div>
            <div class="mt-1 text-2xl font-black">{{ fa_price($payment->amount) }}</div>
        </div>
        <div class="space-y-3 p-6 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">پذیرنده</span><span class="font-semibold">{{ config('site.name') }}</span></div>
            <div class="flex justify-between gap-4"><span class="text-slate-500">شرح</span><span class="text-left font-semibold">{{ $payment->title }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">شماره سفارش</span><span class="font-mono" dir="ltr">{{ $payment->receiptNumber() }}</span></div>

            @if ($payment->isPending())
                @error('gateway')
                    <p class="rounded-xl bg-rose-50 px-3 py-2 text-xs leading-6 text-rose-700">{{ $message }}</p>
                @enderror

                @if ($gateways)
                    <form method="POST" action="{{ route('payments.start', $payment) }}" class="space-y-3 pt-2" x-data="{ sending: false }" x-on:submit="sending = true">
                        @csrf
                        <div class="font-semibold">انتخاب درگاه پرداخت</div>
                        <div class="grid gap-2 {{ count($gateways) > 1 ? 'sm:grid-cols-2' : '' }}">
                            @foreach ($gateways as $name => $label)
                                <label wire:key="gateway-{{ $name }}" class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 py-2.5 transition has-checked:border-emerald-500 has-checked:bg-emerald-50 has-checked:ring-1 has-checked:ring-emerald-500">
                                    <input type="radio" name="gateway" value="{{ $name }}" @checked($selected === $name) class="accent-emerald-600">
                                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-xs font-black text-slate-600">{{ mb_substr($label, 0, 1) }}</span>
                                    <span class="font-semibold">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        @if (isset($gateways['local']))
                            <p class="rounded-xl bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-700">«درگاه تست» فقط برای آزمایش است و پولی کسر نمی‌کند؛ روی سایت اصلی نمایش داده نمی‌شود.</p>
                        @endif

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="submit" class="cursor-pointer btn-success" x-bind:disabled="sending">
                                <span x-show="! sending">پرداخت</span>
                                <span x-show="sending" x-cloak>در حال اتصال…</span>
                            </button>
                            <button type="button" wire:click="cancel" wire:confirm="این پرداخت لغو شود؟" class="cursor-pointer btn-light">انصراف</button>
                        </div>
                        <p class="text-center text-xs text-slate-400">🔒 پرداخت در صفحه امن بانک (شاپرک) انجام می‌شود.</p>
                    </form>
                @else
                    <p class="rounded-xl bg-slate-100 px-3 py-2 text-center leading-6">در حال حاضر درگاه پرداختی فعال نیست. لطفاً کمی بعد دوباره سر بزنید.</p>
                    <button type="button" wire:click="cancel" class="cursor-pointer btn-light w-full">انصراف</button>
                @endif
            @else
                <p class="rounded-xl bg-slate-100 px-3 py-2 text-center">این پرداخت قبلاً {{ $payment->isPaid() ? 'انجام' : 'لغو' }} شده است.</p>
                <a href="{{ route('dashboard.payments') }}" wire:navigate class="cursor-pointer btn-light w-full">پرداخت‌های من</a>
            @endif
        </div>
    </div>
</div>
