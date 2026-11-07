<div class="space-y-6">
    <h1 class="text-2xl font-extrabold">پرداخت‌ها و درآمد</h1>

    {{-- درگاه‌ها از PAYMENT_GATEWAYS در فایل env فعال می‌شوند --}}
    <div class="card flex flex-wrap items-center gap-2 p-4 text-sm">
        <span class="text-slate-500">درگاه‌های فعال:</span>
        @forelse ($activeGateways as $label)
            <span class="chip bg-emerald-50 text-emerald-700">{{ $label }}</span>
        @empty
            <span class="chip bg-rose-50 text-rose-700">هیچ درگاهی فعال نیست؛ کاربران نمی‌توانند پرداخت کنند</span>
        @endforelse
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['امروز', $revenue['today']], ['۳۰ روز اخیر', $revenue['month']], ['کل آگهی ویژه', $revenue['featured']], ['کل نشان تأیید', $revenue['verify']]] as [$label, $value])
            <div class="card p-5">
                <div class="text-sm text-slate-500">{{ $label }}</div>
                <div class="mt-2 text-xl font-black">{{ fa_price($value) }}</div>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-2">
        <select wire:model.live="type" class="input w-auto py-2">
            <option value="">همه خدمات</option>
            <option value="featured">آگهی ویژه</option>
            <option value="verify">نشان تأیید</option>
        </select>
        <select wire:model.live="status" class="input w-auto py-2">
            <option value="">همه وضعیت‌ها</option>
            <option value="paid">موفق</option>
            <option value="pending">در انتظار</option>
            <option value="canceled">لغو شده</option>
        </select>
        <select wire:model.live="gateway" class="input w-auto py-2">
            <option value="">همه درگاه‌ها</option>
            @foreach ($usedGateways as $name => $label)
                <option value="{{ $name }}">{{ $label }}</option>
            @endforeach
        </select>
        <span class="ms-auto self-center text-sm text-slate-500">جمع: <b class="text-slate-800">{{ fa_price($sum) }}</b></span>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-100">
                <tr><th class="th">شماره</th><th class="th">کاربر</th><th class="th">شرح</th><th class="th">مبلغ</th><th class="th">تاریخ</th><th class="th">درگاه</th><th class="th">کد پیگیری</th><th class="th"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($payments as $payment)
                    <tr wire:key="admin-payment-{{ $payment->id }}">
                        <td class="td font-mono text-xs" dir="ltr">{{ $payment->receiptNumber() }}</td>
                        <td class="td">{{ $payment->user->name }}</td>
                        <td class="td max-w-64 truncate">{{ $payment->title }}</td>
                        <td class="td font-semibold">{{ fa_price($payment->amount) }}</td>
                        <td class="td text-slate-500">{{ fa_date($payment->paid_at ?? $payment->created_at) }}</td>
                        <td class="td text-xs">
                            {{ $payment->gatewayLabel() ?? "—" }}
                            @if ($payment->isPending() && $payment->failure_reason)
                                <div class="mt-0.5 max-w-48 truncate text-rose-600" title="{{ $payment->failure_reason }}">{{ $payment->failure_reason }}</div>
                            @endif
                        </td>
                        <td class="td text-xs">
                            {{ $payment->gatewayLabel() ?? '—' }}
                            @if ($payment->isPending() && $payment->failure_reason)
                                <div class="mt-0.5 max-w-48 truncate text-rose-600" title="{{ $payment->failure_reason }}">{{ $payment->failure_reason }}</div>
                            @endif
                        </td>
                        <td class="td font-mono text-xs">{{ $payment->ref_id ?? '—' }}</td>
                        <td class="td">
                            @if ($payment->isPaid())
                                <a href="{{ route('receipts.show', $payment) }}" target="_blank" class="cursor-pointer text-xs text-violet-700 hover:underline">رسید</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="td py-10 text-center text-slate-400">پرداختی پیدا نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</div>
