<?php

use RalphJSmit\Laravel\SEO\Models\SEO;

/*
|--------------------------------------------------------------------------
| تنظیمات پکیج ralphjsmit/laravel-seo
|--------------------------------------------------------------------------
| تگ‌های <title>، description، canonical، robots، Open Graph، Twitter و JSON-LD
| همه از این پکیج ساخته می‌شوند. هر صفحه داده سئوی خودش را با App\Support\Seo::set()
| مشخص می‌کند و قالب اصلی آن را با App\Support\Seo::render() چاپ می‌کند.
*/

return [
    // مدلی که عنوان و توضیح دلخواه صاحب پیج در آن ذخیره می‌شود (جدول seo)
    'model' => SEO::class,

    'site_name' => 'نشونی',

    // تگ <link rel="sitemap"> در همه صفحات
    'sitemap' => '/sitemap.xml',

    'canonical_link' => true,

    'robots' => [
        'default' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
        'force_default' => false,
    ],

    'favicon' => '/favicon.svg',

    'title' => [
        // عنوان صفحه از روی آدرس حدس زده نشود (برای فارسی معنی ندارد)
        'infer_title_from_url' => false,
        'suffix' => ' | نشونی',
        'homepage_title' => 'نشونی | ویترین پیج و کانال کسب‌وکارها در اینستاگرام، تلگرام و ایتا',
    ],

    'description' => [
        'fallback' => 'نشونی ویترین پیج‌ها و کانال‌های کسب‌وکارهای ایرانی در اینستاگرام، تلگرام، ایتا، روبیکا، بله و واتساپ است. پیج مطمئن را بر اساس دسته، شهر و نظر خریداران پیدا کنید یا پیج خود را رایگان ثبت کنید.',
    ],

    'image' => [
        // تصویر پیش‌فرض اشتراک‌گذاری (۱۲۰۰×۶۳۰)
        'fallback' => 'images/og-default.png',
    ],

    'author' => [
        'fallback' => null,
    ],

    'twitter' => [
        '@username' => null,
    ],
];
