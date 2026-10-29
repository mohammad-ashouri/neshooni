<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * پرداخت آگهی ویژه یا نشان تأیید.
 * ابتدا با وضعیت pending ساخته می‌شود و بعد از برگشت از درگاه paid یا canceled می‌شود.
 */
#[Fillable(['user_id', 'type', 'listing_id', 'verification_request_id', 'days', 'amount', 'title', 'status', 'ref_id', 'paid_at'])]
class Payment extends Model
{
    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
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
