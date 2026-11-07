<?php

namespace App\Services;

use App\Models\Payment;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Payment as PaymentManager;
use Shetabit\Multipay\RedirectionForm;

/**
 * اتصال پرداخت‌های سایت به درگاه‌های پکیج shetabit/payment.
 *
 * روند: start() فاکتور را در درگاه ثبت و فرم انتقال به درگاه را برمی‌گرداند؛
 * درگاه کاربر را به payments.callback برمی‌گرداند و verify() تراکنش را از خود درگاه استعلام می‌کند.
 * فهرست و اطلاعات درگاه‌ها در config/payment.php است.
 */
class PaymentGateways
{
    /** تنظیماتی که آدرس بازگشت از درگاه هستند (نامشان در درگاه‌ها فرق دارد) */
    private const CALLBACK_KEYS = ['callbackUrl', 'success_url', 'cancel_url', 'successReturnUrl', 'failureReturnUrl', 'fallbackUrl'];

    /** کلیدهای احتمالی شماره کارت در رسید درگاه‌ها */
    private const CARD_KEYS = ['cardNo', 'cardNumber', 'card_number', 'CardNumber', 'cardPan', 'maskedCardNumber', 'hashedCardNumber', 'card_pan'];

    /**
     * درگاه‌هایی که کاربر می‌تواند انتخاب کند: نام => عنوان فارسی.
     *
     * @return array<string, string>
     */
    public function available(): array
    {
        $rates = config('payment.rates', []);

        return collect(config('payment.enabled', []))
            ->unique()
            ->filter(fn (string $name) => isset(config('payment.map')[$name], config('payment.drivers')[$name]))
            // درگاه تست هیچ‌وقت روی سایت اصلی نشان داده نمی‌شود
            ->reject(fn (string $name) => $name === 'local' && app()->isProduction())
            // درگاه ارزی بدون نرخ تبدیل قابل استفاده نیست
            ->reject(fn (string $name) => array_key_exists($name, $rates) && $rates[$name] <= 0)
            ->mapWithKeys(fn (string $name) => [$name => config("payment.labels.{$name}", $name)])
            ->all();
    }

    public function isAvailable(?string $name): bool
    {
        return $name !== null && array_key_exists($name, $this->available());
    }

    /**
     * ثبت فاکتور در درگاه و گرفتن فرم انتقال کاربر به آن.
     * شناسه تراکنش و جزئیات لازم برای تأیید روی پرداخت ذخیره می‌شود.
     */
    public function start(Payment $payment, string $gateway): RedirectionForm
    {
        $details = $this->details($payment, $gateway);

        $invoice = (new Invoice)->amount($this->amountFor($gateway, $payment->amount));
        $invoice->detail($details);

        $transactionId = null;
        $manager = $this->manager($gateway, $payment)
            ->purchase($invoice, function ($driver, $id) use (&$transactionId) {
                $transactionId = $id;
            });

        $payment->update([
            'gateway' => $gateway,
            'transaction_id' => (string) $transactionId,
            // بعضی درگاه‌ها (مثل اعتبارینو) uuid فاکتور را هنگام ثبت عوض می‌کنند و در تأیید لازم دارند
            'gateway_meta' => ['uuid' => $invoice->getUuid(), 'details' => $details],
            'failure_reason' => null,
        ]);

        return $manager->pay();
    }

    /**
     * استعلام تراکنش از درگاه بعد از بازگشت کاربر.
     * اگر پرداخت انجام نشده باشد یا مبلغ نخواند، درگاه استثنا می‌دهد.
     */
    public function verify(Payment $payment): ReceiptInterface
    {
        $meta = $payment->gateway_meta ?? [];

        return $this->manager($payment->gateway, $payment)
            ->amount($this->amountFor($payment->gateway, $payment->amount))
            ->transactionId($payment->transaction_id)
            ->detail([...($meta['details'] ?? []), 'uuid' => $meta['uuid'] ?? null])
            ->verify();
    }

    /** شماره کارت پرداخت‌کننده اگر درگاه آن را در رسید برگرداند */
    public function cardNumber(ReceiptInterface $receipt): ?string
    {
        foreach (self::CARD_KEYS as $key) {
            $value = $receipt->getDetail($key);
            if (is_scalar($value) && (string) $value !== '') {
                return mb_substr((string) $value, 0, 30);
            }
        }

        return null;
    }

    /**
     * مبلغی که به درگاه داده می‌شود.
     * درگاه‌های داخلی تومان می‌گیرند و خود پکیج به ریال تبدیل می‌کند؛ ارزی‌ها با نرخ تنظیم‌شده.
     */
    public function amountFor(string $gateway, int $toman): int|float
    {
        $rate = config("payment.rates.{$gateway}");
        if ($rate !== null && $rate > 0) {
            return round($toman / $rate, 2);
        }

        // دیجیفای تبدیل واحد ندارد و مبلغ را ریالی می‌خواهد
        return $gateway === 'digify' ? $toman * 10 : $toman;
    }

    /** مدیر پرداخت پکیج با آدرس بازگشت و توضیح همین پرداخت */
    private function manager(string $gateway, Payment $payment): PaymentManager
    {
        /** @var PaymentManager $manager */
        $manager = app(PaymentManager::class);
        $manager->via($gateway);

        $settings = config("payment.drivers.{$gateway}", []);
        $callback = route('payments.callback', $payment);

        foreach (self::CALLBACK_KEYS as $key) {
            if (array_key_exists($key, $settings)) {
                $manager->config($key, $callback);
            }
        }

        if (array_key_exists('description', $settings)) {
            $manager->config('description', $payment->title);
        }

        // درگاه «تومن» (پرداخت امن) اطلاعات معامله را از تنظیمات می‌خواند
        if ($gateway === 'toman') {
            $manager->config('data', [
                'price' => $payment->amount * 10,
                'return_url' => $callback,
                'title' => $payment->title,
            ]);
        }

        return $manager;
    }

    /**
     * جزئیات فاکتور. هر درگاه بخشی از این‌ها را لازم دارد (موبایل برای کارت‌به‌نام، سبد برای اقساطی‌ها).
     *
     * @return array<string, mixed>
     */
    private function details(Payment $payment, string $gateway): array
    {
        $user = $payment->user;
        // شماره سفارش برای هر تلاش یکتاست؛ بعضی بانک‌ها شماره تکراری را رد می‌کنند
        $orderId = $payment->id.now()->format('His');
        $rial = $payment->amount * 10;

        $details = [
            'description' => $payment->title,
            'orderId' => $orderId,
            'order_id' => $orderId,
            'invoiceNumber' => $orderId,
            'invoice_number' => $orderId,
            'mobile' => $user->mobile,
            'phone' => $user->mobile,
            'cellphone' => $user->mobile,
            'name' => $user->name,
        ];

        return match ($gateway) {
            'etebarino' => $details + ['items' => [['productGroup' => 1000, 'amount' => $rial, 'description' => $payment->title]]],
            'snapppay' => $details + ['cartList' => [[
                'cartId' => $payment->id,
                'cartItems' => [['amount' => $payment->amount, 'category' => $payment->typeLabel(), 'count' => 1, 'id' => $payment->id, 'name' => $payment->title, 'commissionType' => 100]],
                'isShipmentIncluded' => false,
                'isTaxIncluded' => false,
                'shippingAmount' => 0,
                'taxAmount' => 0,
                'totalAmount' => $payment->amount,
            ]]],
            'torobpay' => $details + ['cartItems' => [['id' => $payment->id, 'name' => $payment->title, 'count' => 1, 'amount' => $payment->amount, 'category' => $payment->typeLabel()]]],
            default => $details,
        };
    }
}
