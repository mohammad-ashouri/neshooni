# نشونی — ویترین پیج و کانال کسب‌وکارها

**neshooni.shop** — کسب‌وکارها ثبت‌نام می‌کنند، پیج‌ها و کانال‌هایشان (اینستاگرام، تلگرام، ایتا، روبیکا، بله، واتساپ، یوتیوب، آپارات و...) را معرفی می‌کنند و بازدیدکننده‌ها آن‌ها را بر اساس دسته، شهر، پلتفرم و امتیاز پیدا می‌کنند.

ساخته‌شده با Laravel 13، Livewire 4 و Tailwind CSS 4.

## شروع سریع

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

ورود نمایشی: مدیر `09120000000` / `admin123` — صاحب کسب‌وکار `09120000001` / `123456`

## مستندات

- [راهنمای استفاده از سایت](docs/guide-site.md) — در سایت: `/help`
- [راهنمای پنل مدیریت](docs/guide-panel.md) — در پنل: `/panel/help`
- [مستندات فنی برای برنامه‌نویس](docs/DEVELOPER.md)
- [نمونه HTML اولیه (دمو)](docs/prototype/demo.html)
