<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use App\Notifications\SiteMessage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * پرداخت آگهی ویژه یا نشان تأیید.
 * ابتدا با وضعیت pending ساخته می‌شود و بعد از برگشت از درگاه paid یا canceled می‌شود.
 * اتصال به درگاه‌ها در App\Services\PaymentGateways است.
 */
#[Fillable(['user_id', 'type', 'listing_id', 'verification_request_id', 'days', 'amount', 'title', 'status', 'gateway', 'transaction_id', 'ref_id', 'card_number', 'gateway_meta', 'failure_reason', 'paid_at'])]
class Payment extends Model
{
    use RecordsActivity;

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'gateway_meta' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function verificationRequest(): BelongsTo
    {
        return $this->belongsTo(VerificationRequest::class);
    }

    public function scopePaid(Builder $query): void
    {
        $query->where('status', 'paid');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * ثبت پرداخت موفق و اعمال خدمت خریداری‌شده.
     * با قفل ردیف انجام می‌شود تا برگشت تکراری از درگاه دو بار اعمال نشود.
     * خروجی false یعنی پرداخت قبلاً ثبت شده بود.
     */
    public function markAsPaid(string $refId, ?string $cardNumber = null): bool
    {
        $done = DB::transaction(function () use ($refId, $cardNumber) {
            $payment = self::lockForUpdate()->findOrFail($this->id);
            if (! $payment->isPending()) {
                return false;
            }

            $payment->update([
                'status' => 'paid',
                'ref_id' => $refId,
                'card_number' => $cardNumber,
                'failure_reason' => null,
                'paid_at' => now(),
            ]);

            // آگهی ویژه: روزها به انتهای ویژه بودن فعلی (یا از امروز) اضافه می‌شود
            if ($payment->type === 'featured' && $listing = $payment->listing) {
                $start = $listing->isFeatured() ? $listing->featured_until : now();
                $listing->update(['featured_until' => $start->copy()->addDays($payment->days)]);
            }

            return true;
        });

        $this->refresh();

        if ($done) {
            $this->user->notify(new SiteMessage('پرداخت «'.$this->title.'» با موفقیت انجام شد.', route('dashboard.payments')));
        }

        return $done;
    }

    /** نام فارسی درگاهی که پرداخت با آن انجام شد */
    public function gatewayLabel(): ?string
    {
        return $this->gateway ? (config("payment.labels.{$this->gateway}") ?? $this->gateway) : null;
    }

    /** صفحه‌ای که بعد از پرداخت موفق کاربر به آن برمی‌گردد */
    public function afterPaymentUrl(): string
    {
        return $this->type === 'featured' ? route('dashboard') : route('dashboard.verification');
    }

    public function typeLabel(): string
    {
        return $this->type === 'featured' ? 'آگهی ویژه' : 'نشان تأیید';
    }

    /** شماره رسید: NSH-000004 */
    public function receiptNumber(): string
    {
        return 'NSH-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
