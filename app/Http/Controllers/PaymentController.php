<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentGateways;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * رفت و برگشت کاربر به درگاه بانک.
 *
 * start:    کاربر در صفحه پرداخت درگاه را انتخاب می‌کند؛ فاکتور در درگاه ثبت و کاربر منتقل می‌شود.
 * callback: درگاه کاربر را برمی‌گرداند؛ تراکنش استعلام و پرداخت ثبت می‌شود (بدون session، routes/gateway.php).
 * finish:   با session کاربر، پیام نتیجه نشان داده و به صفحه مناسب فرستاده می‌شود.
 */
class PaymentController extends Controller
{
    public function start(Request $request, Payment $payment, PaymentGateways $gateways): Response
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        if (! $payment->isPending()) {
            return redirect()->route('checkout', $payment);
        }

        $gateway = (string) $request->input('gateway');
        if (! $gateways->isAvailable($gateway)) {
            return redirect()->route('checkout', $payment)->withErrors(['gateway' => 'یک درگاه پرداخت انتخاب کنید.']);
        }

        try {
            $form = $gateways->start($payment, $gateway);
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('checkout', $payment)
                ->withErrors(['gateway' => 'اتصال به درگاه «'.config("payment.labels.{$gateway}", $gateway).'» برقرار نشد. چند دقیقه بعد دوباره امتحان کنید یا درگاه دیگری را انتخاب کنید.']);
        }

        // درگاه تست صفحه خودش را در قالب سایت دارد
        if ($gateway === 'local') {
            return response()->view('payments.test-gateway', ['payment' => $payment, 'inputs' => $form->getInputs()]);
        }

        return response()->view('payments.redirect', [
            'action' => $form->getAction(),
            'method' => $form->getMethod(),
            'inputs' => $form->getInputs(),
            'gateway' => config("payment.labels.{$gateway}", $gateway),
        ]);
    }

    public function callback(Request $request, Payment $payment, PaymentGateways $gateways): RedirectResponse
    {
        if ($payment->isPending() && $payment->gateway && $payment->transaction_id) {
            try {
                // درگاه تست خودش چیزی را استعلام نمی‌کند؛ دست‌کم شناسه باید با همین پرداخت بخواند
                if ($payment->gateway === 'local' && (string) $request->input('transactionId') !== $payment->transaction_id) {
                    throw new InvalidPaymentException('شناسه تراکنش معتبر نیست.');
                }

                $receipt = $gateways->verify($payment);
                $payment->markAsPaid((string) $receipt->getReferenceId(), $gateways->cardNumber($receipt));
            } catch (InvalidPaymentException $e) {
                $payment->update(['failure_reason' => Str::limit($e->getMessage() ?: 'پرداخت انجام نشد.', 250)]);
            } catch (Throwable $e) {
                report($e);
                $payment->update(['failure_reason' => 'نتیجه پرداخت از درگاه دریافت نشد. اگر مبلغی کسر شده، طبق قوانین شاپرک حداکثر تا ۷۲ ساعت به حسابتان برمی‌گردد.']);
            }
        }

        return redirect()->route('payments.finish', $payment);
    }

    public function finish(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        if ($payment->isPaid()) {
            return redirect($payment->afterPaymentUrl())
                ->with('toast', ['message' => 'پرداخت موفق بود. کد پیگیری: '.fa_digits($payment->ref_id)]);
        }

        if ($payment->status === 'canceled') {
            return redirect()->route('dashboard.payments');
        }

        return redirect()->route('checkout', $payment)
            ->withErrors(['gateway' => $payment->failure_reason ?? 'پرداخت انجام نشد.']);
    }
}
