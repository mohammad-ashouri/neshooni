<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * حالت تعمیر سایت که مدیر از پنل روشن و خاموش می‌کند.
 * وقتی روشن است همه صفحه ۵۰۳ می‌بینند و فقط مدیر سایت را عادی می‌بیند و می‌تواند وارد شود.
 * برخلاف «php artisan down» در دیتابیس ذخیره می‌شود، پس مدیر بدون دسترسی به سرور هم آن را کنترل می‌کند.
 */
class Maintenance
{
    private const KEY = 'maintenance';

    /** @return array{message: ?string, ends_at: ?string, started_at: string, by: ?int}|null */
    public static function state(): ?array
    {
        return Setting::get(self::KEY);
    }

    public static function isActive(): bool
    {
        return static::state() !== null;
    }

    public static function message(): ?string
    {
        return static::state()['message'] ?? null;
    }

    /** زمان تقریبی بازگشت سایت که مدیر اعلام کرده (اگر گذشته باشد دیگر نمایش داده نمی‌شود) */
    public static function endsAt(): ?CarbonImmutable
    {
        $endsAt = static::state()['ends_at'] ?? null;

        return $endsAt ? CarbonImmutable::parse($endsAt) : null;
    }

    public static function startedAt(): ?CarbonImmutable
    {
        $startedAt = static::state()['started_at'] ?? null;

        return $startedAt ? CarbonImmutable::parse($startedAt) : null;
    }

    public static function enable(?string $message, ?CarbonImmutable $endsAt, ?User $by): void
    {
        Setting::put(self::KEY, [
            'message' => $message ?: null,
            'ends_at' => $endsAt?->toIso8601String(),
            'started_at' => static::startedAt()?->toIso8601String() ?? now()->toIso8601String(),
            'by' => $by?->id,
        ]);
    }

    public static function disable(): void
    {
        Setting::put(self::KEY, null);
    }
}
