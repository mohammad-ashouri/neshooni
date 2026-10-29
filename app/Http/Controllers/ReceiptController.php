<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\Gate;

/**
 * رسید پرداخت قابل چاپ (A5) و صفحه استعلام رسید با QR.
 */
class ReceiptController extends Controller
{
    public function show(Payment $payment)
    {
        abort_unless($payment->user_id === auth()->id() || Gate::allows('payments.view'), 403);
        abort_unless($payment->isPaid(), 404);

        $verifyUrl = rtrim(config('site.url'), '/').'/r/'.$payment->ref_id;

        return view('receipts.show', [
            'payment' => $payment->load('user', 'listing'),
            // QR به صورت تصویر SVG (data URI) ساخته می‌شود
            'qr' => (new QRCode)->render($verifyUrl),
        ]);
    }

    /** با اسکن QR روی رسید، اصل بودن آن نمایش داده می‌شود (بدون اطلاعات شخصی کامل) */
    public function verify(string $ref)
    {
        $payment = Payment::paid()->where('ref_id', en_digits($ref))->firstOrFail();

        return view('receipts.verify', ['payment' => $payment]);
    }
}
