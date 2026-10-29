<x-dashboard-shell title="پرداخت‌های من">
    @if ($payments->isEmpty())
        <x-empty icon="💳" title="هنوز پرداختی نداشته‌اید" />
    @else
        <div class="card mb-4 flex items-center justify-between p-5">
            <span class="text-sm text-slate-500">جمع پرداخت‌های موفق</span>
            <span class="text-xl font-black">{{ fa_price($total) }}</span>
        </div>

        <div class="card overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100">
                    <tr><th class="th">شماره</th><th class="th">شرح</th><th class="th">مبلغ</th><th class="th">تاریخ</th><th class="th">وضعیت</th><th class="th"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($payments as $payment)
                        <tr wire:key="payment-{{ $payment->id }}">
                            <td class="td font-mono text-xs" dir="ltr">{{ $payment->receiptNumber() }}</td>
                            <td class="td"><span class="chip me-1 bg-slate-100 text-slate-600">{{ $payment->typeLabel() }}</span> {{ $payment->title }}</td>
                            <td class="td font-semibold">{{ fa_price($payment->amount) }}</td>
                            <td class="td text-slate-500">{{ fa_date($payment->paid_at ?? $payment->created_at) }}</td>
                            <td class="td">
                                @switch($payment->status)
                                    @case('paid') <span class="chip bg-emerald-50 text-emerald-700">موفق</span> @break
                                    @case('pending') <span class="chip bg-amber-50 text-amber-700">در انتظار پرداخت</span> @break
                                    @default <span class="chip bg-slate-100 text-slate-500">لغو شده</span>
                                @endswitch
                            </td>
                            <td class="td text-left">
                                @if ($payment->isPaid())
                                    <a href="{{ route('receipts.show', $payment) }}" target="_blank" class="btn-light py-1.5 text-xs">🧾 رسید</a>
                                @elseif ($payment->status === 'pending')
                                    <a href="{{ route('checkout', $payment) }}" wire:navigate class="btn-primary py-1.5 text-xs">پرداخت</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $payments->links() }}</div>
    @endif
</x-dashboard-shell>
