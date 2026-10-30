<?php

use Illuminate\Support\Carbon;

test('رقم‌ها بین فارسی و انگلیسی تبدیل می‌شوند', function () {
    expect(fa_digits('09121234567'))->toBe('۰۹۱۲۱۲۳۴۵۶۷')
        ->and(en_digits('۰۹۱۲٣٤٥'))->toBe('0912345')
        ->and(fa_number(12500))->toBe('۱۲٬۵۰۰')
        ->and(fa_price(99000))->toBe('۹۹٬۰۰۰ تومان');
});

test('مبلغ به حروف نوشته می‌شود', function (int $amount, string $words) {
    expect(fa_words($amount))->toBe($words);
})->with([
    [290000, 'دویست و نود هزار'],
    [150000, 'صد و پنجاه هزار'],
    [1015, 'یک هزار و پانزده'],
    [0, 'صفر'],
]);

test('نامک فارسی برای آدرس ساخته می‌شود', function () {
    expect(fa_slug('گالری ماهور | مانتو'))->toBe('گالری-ماهور-مانتو')
        ->and(fa_slug('كافه   دارچين ۲'))->toBe('کافه-دارچین-2')
        ->and(fa_slug('Shop.Name!'))->toBe('shop-name')
        ->and(fa_slug('!!!'))->toBe('');
});

test('تاریخ شمسی نمایش داده می‌شود', function () {
    expect(fa_date(Carbon::parse('2026-10-29 12:00', 'Asia/Tehran')))->toBe('۷ آبان ۱۴۰۵');
});
