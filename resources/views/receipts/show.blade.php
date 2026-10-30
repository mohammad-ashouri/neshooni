{{--
    رسید چاپی A5. صفحه مستقل است (بدون هدر و فوتر سایت) تا چاپ دقیقاً یک صفحه شود.
    رنگ‌ها ثابت و روشن هستند تا در حالت تیره هم روی کاغذ یکسان باشد.
--}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>رسید {{ $payment->receiptNumber() }} | {{ config('site.name') }}</title>
    @vite('resources/css/app.css')
    <style>
        body { background: #e2e8f0; margin: 0; }
        .toolbar { max-width: 148mm; margin: 16px auto; display: flex; gap: 8px; align-items: center; }
        .rc { width: 148mm; height: 210mm; margin: 0 auto 24px; padding: 10mm 11mm; background: #fff; color: #0f172a; font-family: Vazirmatn, Tahoma, sans-serif; font-size: 10.5pt; line-height: 1.7; position: relative; box-sizing: border-box; overflow: hidden; box-shadow: 0 20px 40px rgb(0 0 0 / .15); }
        .rc * { box-sizing: border-box; }
        .rc-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 6mm; padding-bottom: 5mm; border-bottom: 2px solid #6d28d9; }
        .rc-brand { display: flex; align-items: center; gap: 3mm; }
        .rc-logo { width: 13mm; height: 13mm; border-radius: 3.5mm; background: linear-gradient(135deg, #7c3aed, #d946ef); color: #fff; display: grid; place-items: center; font-weight: 900; font-size: 18pt; }
        .rc-brand b { display: block; font-size: 15pt; font-weight: 900; line-height: 1.2; }
        .rc-brand small { color: #64748b; font-size: 8pt; }
        .rc-title { text-align: left; }
        .rc-title h1 { margin: 0; font-size: 15pt; font-weight: 900; }
        .rc-title div { color: #475569; font-size: 8.5pt; }
        .rc-parties { display: grid; grid-template-columns: 1fr 1fr; gap: 4mm; margin-top: 6mm; }
        .rc-box { border: 1px solid #e2e8f0; border-radius: 2.5mm; padding: 3mm 4mm; }
        .rc-box h2 { margin: 0 0 1.5mm; font-size: 8.5pt; color: #6d28d9; font-weight: 700; }
        .rc-box p { margin: 0; font-size: 9pt; }
        .rc-table { width: 100%; border-collapse: collapse; margin-top: 6mm; font-size: 9.5pt; }
        .rc-table th { background: #f1f5f9; color: #334155; font-weight: 700; text-align: right; padding: 2mm 3mm; border-bottom: 1px solid #cbd5e1; }
        .rc-table td { padding: 2.5mm 3mm; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .rc-table .num { text-align: left; white-space: nowrap; }
        .rc-sum { display: flex; justify-content: space-between; align-items: center; gap: 6mm; margin-top: 3mm; }
        .rc-totals { width: 62mm; font-size: 9.5pt; }
        .rc-totals div { display: flex; justify-content: space-between; padding: 1mm 0; }
        .rc-totals .grand { border-top: 1.5px solid #0f172a; margin-top: 1mm; padding-top: 2mm; font-weight: 900; font-size: 11pt; }
        .rc-stamp { transform: rotate(-10deg); white-space: nowrap; border: 2.5px solid #059669; color: #059669; border-radius: 3mm; padding: 1mm 4mm; font-weight: 900; font-size: 12pt; opacity: .85; }
        .rc-words { margin-top: 3mm; padding: 2.5mm 4mm; background: #f5f3ff; border-radius: 2.5mm; font-size: 9pt; }
        .rc-pay { display: flex; gap: 5mm; align-items: center; margin-top: 6mm; padding-top: 5mm; border-top: 1px dashed #cbd5e1; }
        .rc-pay dl { flex: 1; margin: 0; display: grid; grid-template-columns: auto 1fr; gap: 1mm 4mm; font-size: 9pt; }
        .rc-pay dt { color: #64748b; }
        .rc-pay dd { margin: 0; font-weight: 600; }
        .rc-qr { text-align: center; font-size: 7pt; color: #64748b; }
        .rc-qr img { width: 24mm; height: 24mm; display: block; margin: 0 auto 1mm; }
        .rc-foot { position: absolute; bottom: 8mm; right: 11mm; left: 11mm; text-align: center; font-size: 7.5pt; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 2mm; }
        .rc, .rc * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        @media screen and (max-width: 640px) { .rc { zoom: .6; } }

        /* چاپ: فقط خود رسید، دقیقاً یک صفحه A5 */
        @page { size: A5 portrait; margin: 0; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .rc { margin: 0; box-shadow: none; break-after: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span style="flex: 1; color: #475569; font-family: Vazirmatn; font-size: 14px">پیش‌نمایش چاپ · A5</span>
        <button onclick="print()" class="cursor-pointer btn-primary">🖨️ چاپ / ذخیره PDF</button>
        <button onclick="history.length > 1 ? history.back() : close()" class="cursor-pointer btn-light">بازگشت</button>
    </div>

    <article class="rc">
        <header class="rc-head">
            <div class="rc-brand">
                <div class="rc-logo">ن</div>
                <div><b>{{ config('site.name') }}</b><small>{{ config('site.tagline') }}</small></div>
            </div>
            <div class="rc-title">
                <h1>رسید پرداخت</h1>
                <div>شماره رسید: <b dir="ltr">{{ $payment->receiptNumber() }}</b></div>
                <div>تاریخ صدور: {{ fa_date($payment->paid_at) }}</div>
            </div>
        </header>

        <section class="rc-parties">
            <div class="rc-box">
                <h2>پرداخت‌کننده</h2>
                <p><b>{{ $payment->user->name }}</b></p>
                <p>موبایل: <span dir="ltr">{{ fa_digits($payment->user->mobile) }}</span></p>
                @if ($payment->user->placeLabel())
                    <p>شهر: {{ $payment->user->placeLabel() }}</p>
                @endif
            </div>
            <div class="rc-box">
                <h2>دریافت‌کننده</h2>
                <p><b>{{ config('site.name') }}</b></p>
                <p dir="ltr" style="text-align: right">{{ parse_url(config('site.url'), PHP_URL_HOST) }} · {{ config('site.contact.email') }}</p>
                <p>تلفن: <span dir="ltr">{{ fa_digits(config('site.contact.phone')) }}</span></p>
            </div>
        </section>

        <table class="rc-table">
            <thead><tr><th style="width: 9mm">ردیف</th><th>شرح خدمت</th><th>مدت</th><th class="num">مبلغ (تومان)</th></tr></thead>
            <tbody>
                <tr>
                    <td>۱</td>
                    <td>
                        <div style="font-weight: 700">{{ $payment->type === 'featured' ? 'آگهی ویژه' : 'نشان تأیید کسب‌وکار' }}</div>
                        <div style="color: #64748b; font-size: 8.5pt">{{ $payment->type === 'featured' ? ($payment->listing?->title ?? $payment->title) : 'بررسی مدارک و اعطای نشان (یک‌بار)' }}</div>
                    </td>
                    <td>{{ $payment->days ? fa_number($payment->days).' روز' : 'دائمی' }}</td>
                    <td class="num">{{ fa_number($payment->amount) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="rc-sum">
            <div class="rc-totals">
                <div><span>جمع</span><span>{{ fa_number($payment->amount) }}</span></div>
                <div><span>تخفیف</span><span>۰</span></div>
                <div class="grand"><span>مبلغ پرداخت‌شده</span><span>{{ fa_price($payment->amount) }}</span></div>
            </div>
            <div class="rc-stamp">پرداخت شد ✓</div>
        </div>
        <div class="rc-words">مبلغ به حروف: <b>{{ fa_words($payment->amount) }} تومان</b></div>

        <section class="rc-pay">
            <dl>
                <dt>روش پرداخت</dt><dd>درگاه پرداخت اینترنتی</dd>
                <dt>کد پیگیری</dt><dd dir="ltr" style="text-align: right">{{ fa_digits($payment->ref_id) }}</dd>
                <dt>زمان پرداخت</dt><dd>{{ fa_datetime($payment->paid_at) }}</dd>
                <dt>وضعیت</dt><dd style="color: #059669">موفق</dd>
            </dl>
            <div class="rc-qr">
                <img src="{{ $qr }}" alt="کد QR استعلام رسید">
                برای استعلام رسید اسکن کنید
            </div>
        </section>

        <footer class="rc-foot">
            این رسید به‌صورت الکترونیکی صادر شده و نیازی به مهر و امضا ندارد · {{ parse_url(config('site.url'), PHP_URL_HOST) }}
        </footer>
    </article>
</body>
</html>
