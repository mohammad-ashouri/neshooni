<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Locked;

/**
 * تایمر دو دقیقه و نیمه کد پیامکی.
 *
 * هر کد ۱۵۰ ثانیه اعتبار دارد و تا تمام شدن این زمان، کد تازه فرستاده نمی‌شود.
 * پایان زمان در کش سرور نگه داشته می‌شود تا با دست‌کاری مرورگر دور زده نشود؛
 * ویژگی $codeExpiresAt فقط برای نمایش شمارش معکوس است.
 */
trait HasCodeTimer
{
    /** مدت اعتبار هر کد و فاصله بین دو ارسال (ثانیه) */
    public const CODE_SECONDS = 150;

    /** زمان پایان اعتبار کد فعلی (unix timestamp) برای شمارش معکوس */
    #[Locked]
    public int $codeExpiresAt = 0;

    /**
     * چند ثانیه تا امکان ارسال دوباره کد مانده است (۰ یعنی همین حالا می‌شود فرستاد).
     * اگر کد قبلی هنوز معتبر باشد، تایمر صفحه هم روی زمان باقی‌مانده همان کد تنظیم می‌شود.
     */
    protected function codeWaitSeconds(string $key): int
    {
        $expiresAt = (int) Cache::get('code-timer:'.$key, 0);
        $wait = max(0, $expiresAt - now()->timestamp);

        if ($wait > 0) {
            $this->codeExpiresAt = $expiresAt;
        }

        return $wait;
    }

    /** شروع تایمر کد تازه */
    protected function startCodeTimer(string $key): void
    {
        $this->codeExpiresAt = now()->timestamp + self::CODE_SECONDS;
        Cache::put('code-timer:'.$key, $this->codeExpiresAt, self::CODE_SECONDS);
    }

    protected function codeWaitMessage(int $seconds): string
    {
        return 'کد قبلی هنوز معتبر است. '.fa_number($seconds).' ثانیه دیگر می‌توانید کد تازه بگیرید.';
    }

    /** خبر دادن به کاربر که کد قبلی هنوز معتبر است و پیامک تازه‌ای نرفت */
    protected function notifyCodeStillValid(int $seconds): void
    {
        $this->dispatch('toast', message: $this->codeWaitMessage($seconds), type: 'info');
    }
}
