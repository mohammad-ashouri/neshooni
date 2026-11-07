<?php

use Illuminate\Support\Str;
use Shetabit\Multipay\Constants\IranCurrency;
use Shetabit\Multipay\Payment;

/*
|--------------------------------------------------------------------------
| درگاه‌های پرداخت (پکیج shetabit/payment)
|--------------------------------------------------------------------------
| آدرس‌های API همه درگاه‌ها از تنظیمات خود پکیج خوانده می‌شود تا با به‌روزرسانی
| پکیج به‌روز بمانند؛ اینجا فقط اطلاعات حساب از env روی آن نوشته می‌شود.
|
| هر تنظیم یک درگاه با این الگو از env خوانده می‌شود:
|   PAYMENT_{نام درگاه}_{نام تنظیم با حروف بزرگ و _}
| مثلاً merchantId زرین‌پال ← PAYMENT_ZARINPAL_MERCHANT_ID
|      mode زرین‌پال       ← PAYMENT_ZARINPAL_MODE (normal | sandbox)
|
| درگاه‌هایی که به کاربر نشان داده می‌شوند با PAYMENT_GATEWAYS (با ویرگول جدا) انتخاب می‌شوند.
| آدرس بازگشت (callbackUrl) لازم نیست؛ برای هر پرداخت خودکار ساخته می‌شود.
| مبالغ سایت به تومان است و خود پکیج برای درگاه‌هایی که ریال می‌گیرند تبدیل می‌کند.
*/

$package = require Payment::getDefaultConfigPath();

// تنظیماتی که برای هر پرداخت در زمان اجرا مقدار می‌گیرند و از env خوانده نمی‌شوند
$runtimeKeys = ['callbackUrl', 'success_url', 'cancel_url', 'successReturnUrl', 'failureReturnUrl', 'fallbackUrl', 'currency', 'description', 'data'];

// درگاه‌های خارجی ارز دیگری می‌گیرند؛ مبلغ تومانی با نرخ PAYMENT_{نام}_RATE (تومان به ازای یک واحد ارز) تبدیل می‌شود
$foreign = ['paypal', 'stripe', 'xendit', 'skrill', 'cryptomus', 'yekpay'];

// نام متغیر env هر تنظیم: merchantId ← MERCHANT_ID، merchantConfigID ← MERCHANT_CONFIG_ID، client_id ← CLIENT_ID
$envName = fn (string $driver, string $key) => 'PAYMENT_'.Str::upper($driver).'_'.Str::upper(preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', '_', $key));

$drivers = [];

foreach ($package['drivers'] as $name => $settings) {
    foreach ($settings as $key => $default) {
        if (in_array($key, $runtimeKeys, true) || is_array($default)) {
            continue;
        }

        $value = env($envName($name, $key));
        if ($value !== null) {
            $settings[$key] = $value;
        }
    }

    // مبلغ‌هایی که سایت به درگاه‌های داخلی می‌دهد همیشه تومان است
    if (! in_array($name, $foreign, true) && array_key_exists('currency', $settings)) {
        $settings['currency'] = IranCurrency::TOMAN;
    }

    if (in_array($name, $foreign, true)) {
        $settings['currency'] = env('PAYMENT_'.Str::upper($name).'_CURRENCY', $settings['currency'] ?? 'USD');
    }

    $drivers[$name] = $settings;
}

// فایل‌های موقت این دو درگاه در storage نگه داشته می‌شوند، نه در ریشه پروژه
$drivers['jibit']['tokenStoragePath'] = storage_path('app/private/jibit/');
$drivers['parspal']['cachePath'] = storage_path('app/private/parspal/');

return [
    // درگاه پیش‌فرض پکیج (برای انتخاب کاربر از enabled استفاده می‌شود)
    'default' => 'local',

    'drivers' => $drivers,

    /*
    | درگاه‌هایی که در صفحه پرداخت به کاربر نشان داده می‌شوند، به همین ترتیب.
    | local درگاه تست است و در محیط production هرگز نمایش داده نمی‌شود.
    */
    'enabled' => array_values(array_filter(array_map('trim', explode(',', (string) env('PAYMENT_GATEWAYS', 'local'))))),

    // نرخ تبدیل درگاه‌های ارزی: هر واحد ارز چند تومان است (خالی = درگاه نمایش داده نمی‌شود)
    'rates' => collect($foreign)->mapWithKeys(fn ($name) => [$name => (float) env('PAYMENT_'.Str::upper($name).'_RATE', 0)])->all(),

    // نام فارسی هر درگاه برای نمایش به کاربر
    'labels' => [
        'local' => 'درگاه تست',
        'zarinpal' => 'زرین‌پال',
        'zibal' => 'زیبال',
        'nextpay' => 'نکست‌پی',
        'payping' => 'پی‌پینگ',
        'vandar' => 'وندار',
        'jibit' => 'جیبیت',
        'paystar' => 'پی‌استار',
        'sep' => 'سامان کیش (SEP)',
        'saman' => 'سامان (نسخه قدیمی)',
        'behpardakht' => 'به‌پرداخت ملت',
        'sadad' => 'سداد (بانک ملی)',
        'parsian' => 'تاپ پارسیان',
        'pasargad' => 'پاسارگاد',
        'irankish' => 'ایران‌کیش',
        'sepehr' => 'سپهر (بانک صادرات)',
        'asanpardakht' => 'آسان‌پرداخت',
        'fanavacard' => 'فن‌آوا کارت',
        'omidpay' => 'امیدپی (سایان کارت)',
        'pna' => 'پرداخت نوین',
        'refah' => 'بانک رفاه',
        'irandargah' => 'ایران‌درگاه',
        'parspal' => 'پارس‌پال',
        'poolam' => 'پولام',
        'sizpay' => 'سیزپی',
        'shepa' => 'شپا',
        'sepordeh' => 'سپرده',
        'aqayepardakht' => 'آقای پرداخت',
        'gooyapay' => 'گویاپی',
        'panapal' => 'پاناپرداخت',
        'payfa' => 'پی‌فا',
        'bitpay' => 'بیت‌پی',
        'minipay' => 'مینی‌پی',
        'novinopay' => 'نوینوپی',
        'rayanpay' => 'رایان‌پی',
        'atipay' => 'آتی‌پی',
        'daracard' => 'داراکارت',
        'bajet' => 'باجت',
        'digify' => 'دیجیفای',
        'digipay' => 'دیجی‌پی',
        'azki' => 'ازکی وام',
        'snapppay' => 'اسنپ‌پی',
        'torobpay' => 'ترب‌پی',
        'etebarino' => 'اعتبارینو',
        'toman' => 'تومن',
        'yekpay' => 'یک‌پی (ارزی)',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe',
        'xendit' => 'Xendit',
        'skrill' => 'Skrill',
        'cryptomus' => 'Cryptomus (رمزارز)',
    ],
];
