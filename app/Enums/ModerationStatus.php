<?php

namespace App\Enums;

/**
 * وضعیت بررسی مدیر برای پیج، نظر، تصویر و درخواست نشان تأیید.
 */
enum ModerationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار تأیید',
            self::Approved => 'تأیید شده',
            self::Rejected => 'رد شده',
        };
    }

    /** کلاس‌های رنگی برچسب وضعیت (Tailwind) */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-700 ring-amber-200',
            self::Approved => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            self::Rejected => 'bg-rose-50 text-rose-700 ring-rose-200',
        };
    }
}
