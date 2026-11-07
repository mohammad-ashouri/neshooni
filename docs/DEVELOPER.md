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
| `platforms` | `base_url` + آیدی = لینک پیج؛ `handle_type` = `username`، `phone` یا `url` (سایت فروشگاهی: آیدی خود دامنه است، مثل `myshop.ir`) |
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

### سئو

از پکیج **`ralphjsmit/laravel-seo`** استفاده می‌شود (تنظیمات: `config/seo.php`).

- هر صفحه در `mount()` داده سئوی خودش را می‌سازد: `Seo::set(new SEOData(title: ..., description: ..., schema: ...))`.
  `App\Support\Seo::render()` در `layouts/app.blade.php` همه تگ‌ها را چاپ می‌کند: `<title>`، description، canonical، robots، Open Graph، Twitter و JSON-LD.
- صفحه‌هایی که در `Seo::PRIVATE_ROUTES` هستند (داشبورد، پنل، ورود، پرداخت) خودکار `noindex` می‌شوند.
- **آدرس خوانا:** `Listing` و `User` از تریت `HasSlugInUrl` استفاده می‌کنند؛ `route('listings.show', $listing)` آدرس `/l/12-گالری-ماهور` می‌سازد.
  فقط عدد اول آدرس مهم است و `Seo::redirectToCanonical()` آدرس قدیمی یا نامک اشتباه را با **۳۰۱** به آدرس اصلی می‌برد.
- **داده ساختاریافته:** صفحه اول `WebSite` (با جعبه جستجو) و `Organization`؛ پیج `LocalBusiness` یا `Organization` با امتیاز، آدرس و ساعات کاری (`Listing::seoData()`)؛
  دسته‌ها `ItemList` و همه صفحه‌ها `BreadcrumbList`؛ تماس با ما `ContactPage`؛ صفحه کسب‌وکار `ProfilePage`.
- **صفحه دسته‌ها:** صفحه‌بندی با لینک واقعی `?page=2` (کامپوننت `x-page-links`)؛ دسته، پلتفرم، استان و شهر صفحه قابل ایندکس می‌سازند
  و جستجو، مرتب‌سازی و «الان باز است» `noindex` با canonical به نسخه بدون فیلتر هستند. صفحه خالی بعد از صفحه اول ۴۰۴ می‌دهد.
  مدیر می‌تواند برای هر دسته متن معرفی، عنوان و توضیح گوگل بنویسد (ستون‌های `intro`، `seo_title`، `seo_description`).
- **ابزار سئوی صاحب پیج** (`Dashboard\SeoTools` در `/dashboard/seo`): پیش‌نمایش نتیجه گوگل، عنوان و توضیح اختصاصی
  (در جدول `seo` پکیج، از طریق رابطه `$listing->seo` / تریت `HasSEO`)، امتیاز ۱۰۰تایی و چک‌لیست (`App\Services\ListingSeoAudit`)،
  کلمات کلیدی پیشنهادی و لینک ابزارهای گوگل. متن گوگل پیج را به صف بررسی نمی‌برد ولی در پنل مدیریت کنار پیج دیده می‌شود.
- `/sitemap.xml` (یک ساعت کش، کلید `sitemap.xml`) و `/robots.txt` از `SitemapController` می‌آیند. robots در محیط غیر production کل سایت را می‌بندد.
- تصویر پیش‌فرض اشتراک‌گذاری: `public/images/og-default.png` (۱۲۰۰×۶۳۰). صفحه‌های خطا در `resources/views/errors` فارسی و `noindex` هستند.
- در production حتماً `APP_URL=https://neshooni.shop` باشد؛ canonical و نقشه سایت از آن ساخته می‌شوند. ریدایرکت www و http به https را در وب‌سرور تنظیم کنید.

### کپچای ورود

فرم ورود از پکیج `mews/captcha` با پروفایل `neshooni` در `config/captcha.php` استفاده می‌کند (۵ رقم، بدون ارقام شبیه به هم).
تصویر از آدرس `/captcha/neshooni` می‌آید. کد در session است و بعد از هر بار بررسی موفق مصرف می‌شود، برای همین
`Auth\Login` بعد از هر تلاش ورود `captchaKey` را عوض می‌کند تا تصویر تازه بارگذاری شود. ورودی با رقم فارسی هم پذیرفته می‌شود.
در تست‌ها `CAPTCHA_DISABLE=true` است (`phpunit.xml`).

### علاقه‌مندی و خبرنامه

`InterestTracker::add($categoryId, $action)` به دسته و همه والدهایش امتیاز می‌دهد (`site.interest_points`).
با رسیدن به `site.interest_threshold`، `NewsletterModal` عمیق‌ترین دسته‌ای را که به حد نصاب رسیده پیشنهاد می‌کند.
رد کردن پیشنهاد، آن دسته و دسته‌های بالاترش را تا `interest_cooldown_days` روز کنار می‌گذارد.
مهمانی که علاقه مشخصی ندارد بعد از `guest_newsletter_after_pages` صفحه خبرنامه عمومی می‌بیند.

برای اینکه مودال مزاحم نباشد (`InterestTracker::canOffer`):
- در هر session حداکثر یک بار، و بین دو پیشنهاد حداقل `newsletter_gap_days` روز (زمان آخرین پیشنهاد در `offered_at`).
- فقط در صفحه‌های `site.newsletter_routes` (صفحه اول، دسته‌ها، پیج و کسب‌وکار).

### سایت فروشگاهی

- سایت فروشگاهی یک `Listing` روی پلتفرم `website` (`handle_type = url`) است؛ پس بررسی، نظر، آمار، سئو و پرداخت مثل پیج است.
- فرم: مسیر `dashboard.websites.create` همان `Dashboard\ListingForm` با پارامتر `website` (قفل‌شده در `isWebsite`). فرم پیج پلتفرم‌های `url` را نشان نمی‌دهد و نمی‌پذیرد.
- آدرس با `Platform::cleanWebsite()` یکدست می‌شود (بدون `https://`، `www`، query و `/` آخر).
- تبلیغ در صفحه‌ها با کامپوننت `<x-website-promo variant="banner|card|inline" />`؛ اگر پلتفرم غیرفعال باشد چیزی نشان نمی‌دهد.
- `register?intent=website` بعد از ثبت‌نام کاربر را به فرم سایت می‌برد.

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

## درگاه‌های پرداخت

با پکیج [shetabit/payment](https://github.com/shetabit/payment)؛ همه ۵۱ درگاه پکیج آماده‌اند و فقط از env فعال می‌شوند.

- **فعال کردن:** `PAYMENT_GATEWAYS=zarinpal,zibal` (به ترتیب نمایش) و اطلاعات هر درگاه با الگوی `PAYMENT_{درگاه}_{تنظیم}`، مثل `PAYMENT_ZARINPAL_MERCHANT_ID`. فهرست کامل در `.env.example` است. بعد از تغییر env روی سرور `php artisan config:cache` بزنید.
- **درگاه تست:** `local` بدون کسر پول موفق/ناموفق را شبیه‌سازی می‌کند و در `APP_ENV=production` خودکار پنهان می‌شود.
- **مبلغ:** همه قیمت‌ها تومان است و `currency` درگاه‌های داخلی در `config/payment.php` روی تومان ثابت شده تا پکیج خودش به ریال تبدیل کند. درگاه‌های ارزی (PayPal، Stripe و...) فقط با `PAYMENT_{درگاه}_RATE` (تومان به ازای یک واحد ارز) نمایش داده می‌شوند.
- **روند:** `Pages\Checkout` (انتخاب درگاه) ← `PaymentController@start` (ثبت فاکتور، ذخیره `transaction_id`، انتقال به بانک) ← `payments.callback` (استعلام از درگاه و `Payment::markAsPaid()`) ← `payments.finish` (پیام نتیجه).
- **مسیر بازگشت** در `routes/gateway.php` و بیرون از گروه web است؛ بانک‌ها با POST بین‌سایتی برمی‌گردند و شروع session آنجا کاربر را از حساب خارج می‌کند. امنیتش از استعلام تراکنش با خود درگاه است.
- `markAsPaid()` با قفل ردیف کار می‌کند؛ برگشت تکراری از بانک خدمت را دو بار اعمال نمی‌کند. خدمت جدیدی که پولی شد، اثرش را همان‌جا اضافه کنید.
- درگاه‌های اقساطی (اسنپ‌پی، ترب‌پی، دیجی‌پی، ازکی) و «تومن» جزئیات سبد خرید می‌خواهند که در `PaymentGateways::details()` ساخته می‌شود؛ قبل از فعال کردنشان با محیط آزمایشی همان سرویس تست کنید.

## اتصال سرویس‌های واقعی (کارهای باقی‌مانده)

| مورد | محل | توضیح |
| --- | --- | --- |
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
