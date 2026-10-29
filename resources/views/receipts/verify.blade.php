<x-layouts::app title="استعلام رسید">
    <div class="mx-auto grid min-h-[60vh] max-w-md place-items-center px-4 py-12">
        <div class="card w-full overflow-hidden text-center">
            <div class="bg-linear-to-l from-emerald-600 to-teal-500 p-6 text-white">
                <div class="mx-auto grid size-12 place-items-center rounded-full bg-white/20 text-2xl">✓</div>
                <h1 class="mt-2 text-lg font-bold">این رسید معتبر است</h1>
            </div>
            <dl class="space-y-2.5 p-6 text-right text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">شماره رسید</dt><dd class="font-mono" dir="ltr">{{ $payment->receiptNumber() }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">بابت</dt><dd>{{ $payment->typeLabel() }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">مبلغ</dt><dd class="font-semibold">{{ fa_price($payment->amount) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">تاریخ پرداخت</dt><dd>{{ fa_datetime($payment->paid_at) }}</dd></div>
            </dl>
        </div>
    </div>
</x-layouts::app>
