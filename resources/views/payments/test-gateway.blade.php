{{--
    درگاه تست (درایور local پکیج): به جای بانک، دو دکمه پرداخت موفق و ناموفق نشان می‌دهد.
    فقط وقتی local در PAYMENT_GATEWAYS باشد و سایت در محیط production نباشد در دسترس است.
--}}
<x-layouts::app title="درگاه تست">
    <div class="mx-auto grid min-h-[70vh] max-w-md place-items-center px-4 py-12">
        <div class="card w-full overflow-hidden shadow-xl">
            <div class="bg-linear-to-l from-amber-500 to-orange-500 px-6 py-5 text-white">
                <div class="text-sm opacity-90">درگاه پرداخت تست</div>
                <div class="mt-1 text-2xl font-black">{{ fa_price($payment->amount) }}</div>
            </div>
            <div class="space-y-3 p-6 text-sm">
                <div class="flex justify-between gap-4"><span class="text-slate-500">شرح</span><span class="text-left font-semibold">{{ $payment->title }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">شماره تراکنش</span><span class="font-mono" dir="ltr">{{ $payment->transaction_id }}</span></div>
                <p class="rounded-xl bg-amber-50 px-3 py-2 text-xs leading-6 text-amber-700">این صفحه جایگزین بانک است و پولی کسر نمی‌شود. نتیجه را انتخاب کنید تا روند بازگشت از درگاه آزمایش شود.</p>
                <div class="grid grid-cols-2 gap-2 pt-2">
                    <a href="{{ $inputs['successUrl'] }}" class="cursor-pointer btn-success">پرداخت موفق</a>
                    <a href="{{ $inputs['cancelUrl'] }}" class="cursor-pointer btn-danger">پرداخت ناموفق</a>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
