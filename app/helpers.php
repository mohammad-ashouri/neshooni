<?php

/*
|--------------------------------------------------------------------------
| توابع کمکی نمایش فارسی
|--------------------------------------------------------------------------
| این فایل از طریق composer.json (autoload.files) همه‌جا در دسترس است.
*/

use Illuminate\Support\Carbon;

if (! function_exists('fa_digits')) {
    /** تبدیل رقم‌های انگلیسی به فارسی: 123 → ۱۲۳ */
    function fa_digits(string|int|float|null $value): string
    {
        return strtr((string) $value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    }
}

if (! function_exists('en_digits')) {
    /** تبدیل رقم‌های فارسی و عربی به انگلیسی (برای ورودی کاربر) */
    function en_digits(?string $value): string
    {
        return strtr((string) $value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}

if (! function_exists('fa_number')) {
    /** عدد با جداکننده هزارگان فارسی: 12500 → ۱۲٬۵۰۰ */
    function fa_number(int|float|null $value, int $decimals = 0): string
    {
        return fa_digits(number_format((float) $value, $decimals, '٫', '٬'));
    }
}

if (! function_exists('fa_price')) {
    function fa_price(int|float|null $amount): string
    {
        return fa_number($amount).' تومان';
    }
}

if (! function_exists('fa_compact')) {
    /** نمایش خلاصه: 48200 → ۴۸٫۲ هزار */
    function fa_compact(int|float|null $value): string
    {
        $value = (float) $value;

        return match (true) {
            $value >= 1_000_000 => fa_number(round($value / 1_000_000, 1), $value % 1_000_000 ? 1 : 0).' میلیون',
            $value >= 1_000 => fa_number(round($value / 1_000, 1), fmod($value, 1000) >= 50 ? 1 : 0).' هزار',
            default => fa_number($value),
        };
    }
}

if (! function_exists('fa_date')) {
    /**
     * تاریخ شمسی با افزونه intl.
     * الگوها: 'd MMMM y' → ۵ آبان ۱۴۰۵ ، 'd MMM' → ۵ آبا ، 'EEEE d MMMM' → سه‌شنبه ۵ آبان
     */
    function fa_date(?DateTimeInterface $date, string $pattern = 'd MMMM y'): string
    {
        if (! $date) {
            return '';
        }

        $formatter = new IntlDateFormatter('fa_IR@calendar=persian', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'Asia/Tehran', IntlDateFormatter::TRADITIONAL, $pattern);

        return $formatter->format($date);
    }
}

if (! function_exists('fa_datetime')) {
    function fa_datetime(?DateTimeInterface $date): string
    {
        return fa_date($date, 'd MMMM y، ساعت HH:mm');
    }
}

if (! function_exists('fa_words')) {
    /** مبلغ به حروف برای رسید: 290000 → دویست و نود هزار */
    function fa_words(int $number): string
    {
        if ($number === 0) {
            return 'صفر';
        }

        $ones = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
        $teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
        $tens = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
        $hundreds = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
        $scales = ['', 'هزار', 'میلیون', 'میلیارد'];

        // سه‌رقمی‌ها را جدا به حروف تبدیل می‌کنیم و با «و» به هم می‌چسبانیم
        $threeDigits = function (int $n) use ($ones, $teens, $tens, $hundreds): string {
            $parts = [];
            if (intdiv($n, 100)) {
                $parts[] = $hundreds[intdiv($n, 100)];
            }
            $rest = $n % 100;
            if ($rest >= 10 && $rest < 20) {
                $parts[] = $teens[$rest - 10];
            } else {
                if (intdiv($rest, 10)) {
                    $parts[] = $tens[intdiv($rest, 10)];
                }
                if ($rest % 10) {
                    $parts[] = $ones[$rest % 10];
                }
            }

            return implode(' و ', $parts);
        };

        $words = [];
        for ($i = 0; $number > 0; $i++, $number = intdiv($number, 1000)) {
            if ($number % 1000) {
                array_unshift($words, trim($threeDigits($number % 1000).' '.$scales[$i]));
            }
        }

        return implode(' و ', $words);
    }
}

if (! function_exists('fa_time')) {
    /** '09:00' → ۹:۰۰ */
    function fa_time(?string $time): string
    {
        return fa_digits(ltrim((string) $time, '0') ?: '0:00');
    }
}

if (! function_exists('tehran_now')) {
    /** زمان فعلی به وقت ایران (برای ساعات کاری «باز/بسته») */
    function tehran_now(): Carbon
    {
        return Carbon::now('Asia/Tehran');
    }
}
