# مستندات فنی نشونی

این سند برای برنامه‌نویسی است که می‌خواهد پروژه را راه بیندازد، بشناسد یا توسعه دهد.

## فناوری‌ها

| بخش | ابزار |
| --- | --- |
| زبان و فریم‌ورک | PHP 8.4، Laravel 13 |
| رابط کاربری | Livewire 4 (کامپوننت‌های کلاسی) + Blade، کمی Alpine (فقط باز و بسته شدن منوها و toast) |
| استایل | Tailwind CSS 4 از طریق Vite، فونت Vazirmatn |
| پایگاه داده | PostgreSQL (روی SQLite و MySQL هم اجرا می‌شود) |
| تست | Pest |
| کتابخانه‌های جانبی | `chillerlan/php-qrcode` (QR رسید)، `qrcode-generator` (QR روی بنر در مرورگر) |

افزونه‌های لازم PHP: `intl` (تاریخ شمسی)، `gd`، `pdo_pgsql`.

## راه‌اندازی

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# تنظیمات DB_* را در .env وارد کنید
php artisan migrate --seed
php artisan storage:link
npm run build        # یا npm run dev هنگام توسعه
php artisan serve
```

داده نمایشی (`DemoSeeder`) فقط خارج از محیط production ساخته می‌شود:

| نقش | موبایل | رمز |
| --- | --- | --- |
| مدیر کل | 09120000000 | admin123 |
| ناظر | 09120000010 | moder123 |
| صاحب کسب‌وکار (گالری ماهور) | 09120000001 | 123456 |

> در production رمزها باید حداقل ۸ کاراکتر با حرف و عدد باشند (`AppServiceProvider::configureDefaults`).

## ساختار پوشه‌ها

```
app/
  Enums/                 ModerationStatus (pending/approved/rejected) و ReportStatus
  Http/Controllers/      فقط کارهای غیر Livewire: خروج، رسید چاپی، راهنما
  Http/Middleware/       CountGuestPages (شمارش صفحه‌های مهمان برای خبرنامه)
  Livewire/
    Pages/               صفحات عمومی: Home, Explore, ListingShow, BusinessProfile, Contact, Checkout
    Auth/                Login, Register, ForgotPassword
    Dashboard/           داشبورد صاحب کسب‌وکار
    Admin/               پنل مدیریت (قالب layouts::admin)
    NotificationBell.php, NewsletterModal.php   (در قالب اصلی)
  Models/
  Notifications/SiteMessage.php                 تنها نوع اعلان داخل سایت (متن + لینک)
  Services/
    InterestTracker.php      تشخیص علاقه‌مندی برای پیشنهاد خبرنامه
    ListingStats.php         ثبت بازدید/کلیک و سری روزانه نمودار
    SubscriptionNotifier.php خبر دادن به مشترکان هنگام انتشار پیج
  helpers.php            توابع نمایش فارسی (fa_number, fa_price, fa_date, fa_words, ...)
config/
  site.php               نام، اطلاعات تماس، قیمت‌ها، اعداد کلیدی
  permissions.php        فهرست دسترسی‌های نقش‌ها
  locations.php          کشور ← استان ← شهر
resources/views/
  layouts/app.blade.php, layouts/admin.blade.php
  partials/              هدر و فوتر
  components/            کارت پیج، آواتار، ستاره، مودال، نمودارها، قاب داشبورد و...
  livewire/              ویوی هر کامپوننت با همان مسیر کلاس
  receipts/              رسید A5 و صفحه استعلام
resources/js/
  app.js                 حالت تیره و toast
  banner.js              کشیدن بنر «ما در نشونی هستیم» روی canvas
lang/fa/                 پیام‌های فارسی اعتبارسنجی و صفحه‌بندی
docs/                    راهنمای سایت و پنل (در /help و /panel/help نمایش داده می‌شود) و نمونه HTML اولیه
```

## قراردادهای کدنویسی

- هر صفحه یک کامپوننت Livewire است و مسیرش در `routes/web.php` مستقیم به کلاس وصل شده.
- منطق تکراری در **مدل** (مثلاً `Listing::hoursStatus()`) یا **سرویس** است، نه در ویو.
- عنوان صفحه با `#[Title]` یا `->title()` و قالب پنل با `#[Layout('layouts::admin')]`.
- پیام کوتاه به کاربر: `$this->dispatch('toast', message: '...', type: 'success|error|info')`؛
  بعد از redirect: `session()->flash('toast', ['message' => '...'])`.
- همه لینک‌های داخلی `wire:navigate` دارند (به‌جز لینک‌هایی که در تب جدید باز می‌شوند).
- مودال‌ها با یک متغیر بولی در کامپوننت باز و بسته می‌شوند: `@if ($showX) <x-modal close="$set('showX', false)">`.
- رقم‌های ورودی کاربر با `en_digits()` و رقم‌های نمایشی با `fa_number()` یا `fa_digits()`.
- قبل از کامیت: `vendor/bin/pint --dirty`.

## مدل داده (خلاصه)

| جدول | توضیح |
| --- | --- |
| `roles` | نقش‌ها؛ `permissions` آرایه JSON از کلیدهای `config/permissions.php` یا `*` برای مدیر کل |
| `users` | ورود با `mobile`؛ `verified_at` = نشان تأیید؛ `interests` = داده InterestTracker |
| `categories` | درخت با `parent_id` تا ۳ سطح |
| `platforms` | `base_url` + آیدی = لینک پیج؛ `handle_type` = `username` یا `phone` |
| `listings` | پیج‌ها؛ `category_id` همیشه عمیق‌ترین دسته انتخاب‌شده؛ `hours` JSON ساعات هفته؛ `featured_until`؛ `published_at` زمان اولین انتشار |
| `listing_photos` | گالری؛ هر عکس وضعیت تأیید جدا دارد |
| `listing_daily_stats` | بازدید و کلیک روزانه هر پیج |
| `follows` | دنبال کردن (کاربر ↔ پیج) |
| `reviews` | یک نظر برای هر کاربر و پیج + پاسخ صاحب پیج |
| `reports` | گزارش تخلف (morph به پیج یا نظر) |
| `payments` | آگهی ویژه یا نشان تأیید؛ pending → paid/canceled |
| `verification_requests` | مدرک (دیسک خصوصی `local`) و وضعیت |
| `subscriptions` | خبرنامه؛ `category_id = null` یعنی عمومی؛ کاربر با `user_id` و مهمان با `contact` |
| `outbox_messages` | صف پیام برای مشترکان مهمان (پیامک/ایمیل) |
| `notifications` | اعلان‌های دیتابیسی Laravel |

### ساختار ساعات کاری

```json
{ "enabled": true, "days": [ { "open": true, "from": "09:00", "to": "21:00" }, ... ] }
```

ایندکس ۰ = شنبه. `to <= from` یعنی تا بامداد روز بعد؛ `00:00` تا `23:59` یعنی شبانه‌روزی.
محاسبه «الان باز است» با ساعت تهران در `Listing::hoursStatus()` است.

## قوانین کسب‌وکار

### بررسی محتوا

- پیج، عکس گالری، نظر و درخواست نشان تأیید همه با `ModerationStatus` و تأیید مدیر منتشر می‌شوند.
- ویرایش اطلاعات اصلی پیج تأییدشده آن را دوباره `pending` می‌کند (`ListingForm::save` با `isDirty()`)؛ تغییر گالری نه.
- در اولین تأیید (`published_at` خالی) `SubscriptionNotifier` به مشترکان دسته پیج و دسته‌های والدش خبر می‌دهد.

### دسترسی‌ها

- هر کلید `config/permissions.php` یک Gate است (`Gate::before` در `AppServiceProvider`)، پس همه‌جا می‌شود نوشت
  `@can('reviews.delete')`، `Gate::authorize(...)` یا `->middleware('can:...')`.
- `access-panel` یعنی نقش حداقل یک دسترسی دارد.
- `roles.manage` عمداً در فهرست نیست؛ فقط نقش `*` آن را دارد تا کسی نتواند به خودش دسترسی بدهد.
- در `Admin\Users` کاربر نقش خودش را تغییر نمی‌دهد و غیر مدیر کل فقط نقش‌هایی را می‌دهد که دسترسی‌هایشان زیرمجموعه دسترسی خودش است.
- اقدام‌های حساس داخل کامپوننت هم دوباره چک می‌شوند (فقط پنهان کردن دکمه کافی نیست).

### علاقه‌مندی و خبرنامه

`InterestTracker::add($categoryId, $action)` به دسته و همه والدهایش امتیاز می‌دهد (`site.interest_points`).
با رسیدن به `site.interest_threshold`، `NewsletterModal` همان دسته را پیشنهاد می‌کند. رد کردن پیشنهاد تا `interest_cooldown_days` روز اعتبار دارد.
مهمانی که علاقه مشخصی ندارد بعد از `guest_newsletter_after_pages` صفحه خبرنامه عمومی می‌بیند.

### آمار

`ListingStats::record()` هر بازدید یا کلیک را در هر نشست یک‌بار می‌شمارد (صاحب پیج شمرده نمی‌شود) و در `listing_daily_stats` جمع می‌کند.

## کارهای معمول

### افزودن کشور

در `config/locations.php` یک کلید دوحرفی ISO با `name` و `provinces` اضافه کنید. فرم‌ها خودکار نشانش می‌دهند.

### افزودن پلتفرم

از پنل → پلتفرم‌ها. برای داده اولیه، `PlatformSeeder` را ویرایش کنید.

### افزودن دسترسی جدید

1. کلید را در گروه مناسب `config/permissions.php` بنویسید (برچسب فارسی).
2. در مسیر یا کامپوننت با `can:کلید` / `Gate::authorize('کلید')` استفاده کنید.
3. اگر بخش پنل است، در `$menu` فایل `layouts/admin.blade.php` اضافه کنید.

### تغییر قیمت‌ها یا اطلاعات تماس

فقط `config/site.php`.

## اتصال سرویس‌های واقعی (کارهای باقی‌مانده)

| مورد | محل | توضیح |
| --- | --- | --- |
| درگاه پرداخت | `Livewire/Pages/Checkout.php` | الان نمایشی است. `pay()` باید به درگاه بانک برود و تأیید تراکنش در callback انجام شود؛ همان منطق تمدید ویژه را نگه دارید. |
| پیامک کد بازیابی | `Livewire/Auth/ForgotPassword.php` | جای `TODO` ارسال پیامک؛ در محیط local کد به‌صورت toast نمایش داده می‌شود. |
| تأیید موبایل هنگام ثبت‌نام | `Livewire/Auth/Register.php` | در صورت نیاز، کد یک‌بارمصرف قبل از ساخت حساب. |
| ارسال خبرنامه مهمان‌ها | `outbox_messages` | یک دستور زمان‌بندی‌شده در `routes/console.php` که پیام‌های بدون `sent_at` را بفرستد. |

## تست

```bash
php artisan test
```

تست‌ها در `tests/Feature` هستند و روی دیتابیس جداگانه (`RefreshDatabase`) اجرا می‌شوند.

## استقرار

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

`APP_ENV=production`، `APP_DEBUG=false` و `SITE_PUBLIC_URL=https://neshooni.shop` را تنظیم کنید (این آدرس روی QR رسید و بنرها می‌نشیند).
