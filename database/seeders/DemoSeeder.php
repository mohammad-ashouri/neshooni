<?php

namespace Database\Seeders;

use App\Enums\ModerationStatus;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Listing;
use App\Models\ListingDailyStat;
use App\Models\Payment;
use App\Models\Platform;
use App\Models\Report;
use App\Models\Review;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Notifications\SiteMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * داده نمونه برای نمایش و توسعه: کسب‌وکارها، پیج‌ها، نظرات، آمار ۹۰ روزه و...
 * رمز همه کاربران نمونه 123456 است (مدیر: admin123 ، ناظر: moder123).
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'key');
        $platforms = Platform::pluck('id', 'key');
        $cat = fn (string $slug) => Category::where('slug', $slug)->value('id');

        // ---------- کاربران ----------
        $user = fn (string $name, string $mobile, string $province, string $city, string $bio, int $daysAgo, string $role = 'user', string $password = '123456') => User::create([
            'name' => $name, 'mobile' => $mobile, 'password' => $password, 'role_id' => $roles[$role],
            'province' => $province, 'city' => $city, 'bio' => $bio,
            'created_at' => now()->subDays($daysAgo),
        ]);

        $admin = $user('مدیر نشونی', '09120000000', 'تهران', 'تهران', 'مدیر سایت', 120, 'admin', 'admin123');
        $mahoor = $user('گالری ماهور', '09120000001', 'تهران', 'تهران', 'فروش آنلاین پوشاک زنانه با ارسال به سراسر ایران.', 60);
        $darchin = $user('کافه دارچین', '09120000002', 'فارس', 'شیراز', 'کافه و شیرینی‌پزی خانگی در قلب شیراز.', 55);
        $parseh = $user('آموزشگاه زبان پارسه', '09120000003', 'خراسان رضوی', 'مشهد', 'آموزش زبان انگلیسی و آیلتس، حضوری و آنلاین.', 50);
        $omid = $user('دیجی‌مارکت امید', '09120000004', 'اصفهان', 'اصفهان', 'فروش گوشی، لوازم جانبی و گجت‌های هوشمند.', 45);
        $niloo = $user('سفال‌خانه نیلو', '09120000005', 'آذربایجان شرقی', 'تبریز', 'سفال و سرامیک دست‌ساز.', 40);
        $arman = $user('باشگاه آرمان', '09120000006', 'البرز', 'کرج', 'بدنسازی، فیتنس و برنامه تمرینی آنلاین.', 30);
        $rose = $user('آرایشی رز', '09120000007', 'گیلان', 'رشت', 'محصولات آرایشی و مراقبت پوست اورجینال.', 25);
        $parvaz = $user('تور پرواز آبی', '09120000008', 'تهران', 'تهران', 'تورهای داخلی و خارجی با بهترین قیمت.', 20);
        $ava = $user('دکوراسیون آوا', '09120000009', 'یزد', 'یزد', 'طراحی داخلی و لوازم دکوری.', 15);
        $user('ناظر نشونی', '09120000010', 'تهران', 'تهران', 'ناظر محتوای سایت', 90, 'moderator', 'moder123');

        $parseh->update(['verified_at' => now()->subDays(30)]);
        $rose->update(['verified_at' => now()->subDays(20)]);

        // ---------- ساعات کاری ----------
        $week = fn (string $from, string $to, ?array $friday = null) => [
            'enabled' => true,
            'days' => array_map(fn (int $i) => $i === 6
                ? ($friday ? ['open' => true, 'from' => $friday[0], 'to' => $friday[1]] : ['open' => false, 'from' => $from, 'to' => $to])
                : ['open' => true, 'from' => $from, 'to' => $to], range(0, 6)),
        ];

        // ---------- پیج‌ها ----------
        $listing = function (User $owner, string $platform, string $handle, string $title, string $category, int $followers, string $description, int $daysAgo, array $extra = []) use ($platforms, $cat) {
            return Listing::create([
                'user_id' => $owner->id,
                'platform_id' => $platforms[$platform],
                'category_id' => $cat($category),
                'handle' => $handle,
                'title' => $title,
                'description' => $description,
                'followers' => $followers,
                'status' => ModerationStatus::Approved,
                'province' => $owner->province,
                'city' => $owner->city,
                'created_at' => now()->subDays($daysAgo),
                ...$extra,
            ]);
        };

        $l = [];
        $l['mahoor_ig'] = $listing($mahoor, 'instagram', 'mahoor.gallery', 'گالری ماهور', 'manteau', 48200, 'پوشاک زنانه، مانتو و شومیز با جدیدترین مدل‌های فصل. ارسال رایگان برای خریدهای بالای یک میلیون تومان.', 58, ['hours' => $week('09:00', '22:00', ['10:00', '20:00']), 'featured_until' => now()->addDays(5)]);
        $l['mahoor_tg'] = $listing($mahoor, 'telegram', 'mahoorgallery', 'گالری ماهور | کانال تخفیف‌ها', 'manteau', 12300, 'اطلاع‌رسانی حراج‌ها، کدهای تخفیف و محصولات جدید گالری ماهور.', 50);
        $l['mahoor_eitaa'] = $listing($mahoor, 'eitaa', 'mahoor_gallery', 'گالری ماهور در ایتا', 'blouse', 900, 'کانال رسمی گالری ماهور در پیام‌رسان ایتا.', 1, ['status' => ModerationStatus::Pending]);
        $l['darchin_ig'] = $listing($darchin, 'instagram', 'darchin.cafe', 'کافه دارچین', 'coffee-shop', 23500, 'قهوه تخصصی، کیک‌های خانگی و صبحانه. هر روز از ۸ صبح تا ۱۱ شب.', 54, ['online_only' => false, 'address' => 'شیراز، خیابان زند، نبش کوچه ۱۲، کافه دارچین', 'phone' => '07132345678', 'hours' => $week('08:00', '23:30', ['08:00', '23:30'])]);
        $l['darchin_bale'] = $listing($darchin, 'bale', 'darchincafe', 'کافه دارچین | سفارش آنلاین', 'homemade-cake', 1800, 'سفارش کیک تولد و شیرینی برای مراسم‌ها.', 30, ['hours' => $week('09:00', '21:00')]);
        $l['parseh_ig'] = $listing($parseh, 'instagram', 'parseh.lang', 'آموزشگاه زبان پارسه', 'english', 61000, 'آموزش مکالمه، گرامر و آمادگی آیلتس با اساتید مجرب. ویدیوهای آموزشی رایگان هر روز.', 49, ['online_only' => false, 'address' => 'مشهد، بلوار سجاد، سجاد ۱۴، پلاک ۸، طبقه دوم', 'phone' => '05138765432', 'hours' => $week('09:00', '20:00'), 'featured_until' => now()->addDays(20)]);
        $l['parseh_tg'] = $listing($parseh, 'telegram', 'parseh_english', 'لغات روزانه انگلیسی پارسه', 'english', 34000, 'هر روز ۱۰ لغت کاربردی انگلیسی همراه با تلفظ و مثال.', 47);
        $l['parseh_yt'] = $listing($parseh, 'youtube', 'parsehacademy', 'پارسه آکادمی', 'english', 9800, 'دوره‌های کامل ویدیویی آموزش زبان انگلیسی.', 40);
        $l['parseh_aparat'] = $listing($parseh, 'aparat', 'parseh', 'پارسه در آپارات', 'english', 5400, 'ویدیوهای آموزشی زبان در آپارات.', 2, ['status' => ModerationStatus::Pending]);
        $l['omid_ig'] = $listing($omid, 'instagram', 'omid.digi', 'دیجی‌مارکت امید', 'phones', 27800, 'گوشی موبایل، هدفون، ساعت هوشمند و لوازم جانبی با گارانتی معتبر.', 44, ['online_only' => false, 'address' => 'اصفهان، خیابان چهارباغ عباسی، پاساژ نور، طبقه همکف، واحد ۲۱', 'phone' => '03132221100', 'hours' => $week('10:00', '21:00')]);
        $l['omid_tg'] = $listing($omid, 'telegram', 'omid_digimarket', 'دیجی‌مارکت امید | قیمت روز', 'phones', 15600, 'قیمت روز گوشی و لوازم جانبی، به‌روزرسانی روزانه.', 42, ['hours' => $week('10:00', '21:00')]);
        $l['omid_rubika'] = $listing($omid, 'rubika', 'omiddigi', 'دیجی‌مارکت امید در روبیکا', 'cases', 4100, 'کانال روبیکای فروشگاه دیجی‌مارکت امید.', 20);
        $l['niloo_ig'] = $listing($niloo, 'instagram', 'niloo.sofal', 'سفال‌خانه نیلو', 'pottery', 8700, 'ظروف سفالی و سرامیکی دست‌ساز با طرح‌های سنتی و مدرن.', 39, ['online_only' => false, 'address' => 'تبریز، بازار تاریخی، راسته کفاشان، سفال‌خانه نیلو', 'hours' => $week('09:30', '19:00')]);
        $l['niloo_eitaa'] = $listing($niloo, 'eitaa', 'niloosofal', 'سفال‌خانه نیلو در ایتا', 'pottery', 2300, 'معرفی محصولات جدید و سفارش ساخت.', 25);
        $l['arman_ig'] = $listing($arman, 'instagram', 'arman.gym', 'باشگاه آرمان', 'bodybuilding', 19400, 'برنامه تمرینی و رژیم غذایی اختصاصی، کلاس‌های فیتنس و TRX.', 29, ['online_only' => false, 'address' => 'کرج، عظیمیه، میدان مهران، باشگاه آرمان', 'phone' => '02632551234', 'hours' => $week('06:00', '23:00', ['08:00', '14:00'])]);
        $l['arman_wa'] = $listing($arman, 'whatsapp', '989121234567', 'مشاوره تمرینی آرمان', 'bodybuilding', 0, 'مشاوره رایگان برنامه تمرینی در واتساپ.', 3, ['status' => ModerationStatus::Rejected, 'reject_reason' => 'شماره واتساپ با اطلاعات کسب‌وکار مطابقت ندارد.']);
        $l['rose_ig'] = $listing($rose, 'instagram', 'rose.beauty', 'آرایشی رز', 'makeup', 35200, 'محصولات آرایشی و مراقبت پوست اورجینال از برندهای معتبر.', 24, ['hours' => $week('10:00', '22:00'), 'featured_until' => now()->addDays(2)]);
        $l['rose_tg'] = $listing($rose, 'telegram', 'rosebeautyshop', 'آرایشی رز | تخفیف‌ها', 'skincare', 7600, 'تخفیف‌های ویژه و معرفی محصولات جدید.', 22);
        $l['parvaz_ig'] = $listing($parvaz, 'instagram', 'parvazabi.tour', 'تور پرواز آبی', 'domestic-tours', 12800, 'تور کیش، مشهد، استانبول و دبی با بهترین قیمت و خدمات.', 19, ['online_only' => false, 'address' => 'تهران، خیابان ولیعصر، بالاتر از پارک ساعی، ساختمان ۱۲۰', 'phone' => '02188776655', 'hours' => $week('09:00', '18:00')]);
        $l['parvaz_soroush'] = $listing($parvaz, 'soroush', 'parvazabi', 'پرواز آبی در سروش', 'turkey-tours', 1500, 'اطلاع‌رسانی تورهای لحظه آخری.', 12);
        $l['ava_ig'] = $listing($ava, 'instagram', 'ava.decor', 'دکوراسیون آوا', 'decor', 22100, 'ایده‌های دکوراسیون، طراحی داخلی و فروش لوازم دکوری.', 14, ['online_only' => false, 'address' => 'یزد، بلوار جمهوری، نمایشگاه دکوراسیون آوا', 'phone' => '03536264400', 'hours' => $week('10:00', '20:00'), 'featured_until' => now()->addDays(12)]);
        $l['ava_tg'] = $listing($ava, 'telegram', 'ava_service', 'خدمات طراحی داخلی آوا', 'interior-design', 3200, 'مشاوره و اجرای طراحی داخلی منزل و محل کار.', 10, ['hours' => $week('09:00', '17:00')]);

        $this->seedGalleries($l);
        $this->seedDailyStats();

        // ---------- دنبال‌کننده‌ها ----------
        $customers = User::whereHas('role', fn ($q) => $q->where('key', 'user'))->get();
        foreach (Listing::approved()->get() as $item) {
            foreach ($customers as $customer) {
                if ($customer->id !== $item->user_id && ($item->id * $customer->id) % 3 !== 1) {
                    $item->followers()->attach($customer->id, ['created_at' => now()->subDays(($item->id + $customer->id) % 20)]);
                }
            }
        }

        // ---------- نظرات ----------
        $review = fn (Listing $item, User $author, int $rating, string $body, int $daysAgo, ?string $reply = null, ModerationStatus $status = ModerationStatus::Approved) => Review::create([
            'listing_id' => $item->id, 'user_id' => $author->id, 'rating' => $rating, 'body' => $body, 'status' => $status,
            'reply' => $reply, 'replied_at' => $reply ? now()->subDays(max(0, $daysAgo - 1)) : null,
            'created_at' => now()->subDays($daysAgo),
        ]);

        $review($l['mahoor_ig'], $darchin, 5, 'کیفیت پارچه‌ها عالی بود و خیلی سریع ارسال کردن. حتماً دوباره خرید می‌کنم.', 20, 'ممنون از اعتمادتون 🌸 خوشحالیم که راضی بودید.');
        $rudeReview = $review($l['mahoor_ig'], $rose, 4, 'مدل‌ها شیک و به‌روز هستن، فقط کاش سایزبندی کامل‌تر بود.', 12);
        $review($l['mahoor_ig'], $niloo, 5, 'پاسخگویی دایرکت خیلی سریع و مودبانه بود.', 5);
        $review($l['darchin_ig'], $mahoor, 5, 'بهترین کیک شکلاتی شیراز! فضای کافه هم خیلی دنجه.', 18, 'لطف دارید! منتظرتون هستیم ☕');
        $review($l['darchin_ig'], $parvaz, 4, 'قهوه‌ها خوب بود، فقط آخر هفته‌ها خیلی شلوغه.', 7);
        $review($l['parseh_ig'], $omid, 5, 'ویدیوهای آموزشی‌شون واقعاً کاربردیه، کلی لغت جدید یاد گرفتم.', 15);
        $review($l['parseh_ig'], $arman, 4, 'کلاس‌های آنلاین منظم و با کیفیت برگزار میشه.', 9);
        $review($l['parseh_tg'], $ava, 5, 'هر روز لغت‌هاشو دنبال می‌کنم، خیلی مفیده.', 6);
        $review($l['omid_ig'], $parseh, 3, 'قیمت‌ها مناسبه ولی ارسال یکم طول کشید.', 10, 'از تأخیر عذرخواهی می‌کنیم؛ ارسال‌ها از این هفته با پست پیشتاز انجام میشه.');
        $review($l['niloo_ig'], $mahoor, 5, 'ظرف‌ها دقیقاً مثل عکس‌ها بود و خیلی خوب بسته‌بندی شده بود.', 8);
        $review($l['rose_ig'], $darchin, 4, 'محصولات اصل بودن و تاریخ انقضای خوبی داشتن.', 4);
        $review($l['mahoor_ig'], $ava, 2, 'سفارشم با یک هفته تأخیر رسید و کسی جواب پیام‌ها رو نمی‌داد.', 1, null, ModerationStatus::Pending);
        $review($l['arman_ig'], $parvaz, 5, 'برنامه تمرینی‌ای که بهم دادن خیلی کمکم کرد، ممنون.', 2, null, ModerationStatus::Pending);

        // ---------- نشان تأیید و پرداخت‌ها ----------
        $document = $this->sampleImage('documents/license-sample.svg', '📄', '#f8fafc', '#e2e8f0');
        $verification = fn (User $owner, string $type, string $status, int $daysAgo, ?string $note = null) => VerificationRequest::create([
            'user_id' => $owner->id, 'business_type' => $type, 'document' => $document, 'note' => $note, 'status' => $status,
            'created_at' => now()->subDays($daysAgo),
        ]);
        $verification($parseh, 'company', 'approved', 31);
        $verification($rose, 'individual', 'approved', 21);
        $omidRequest = $verification($omid, 'individual', 'pending', 1, 'جواز کسب صنف موبایل و لوازم جانبی');

        $payment = fn (User $owner, string $type, int $amount, ?int $days, ?Listing $item, string $title, int $daysAgo, ?int $requestId = null) => Payment::create([
            'user_id' => $owner->id, 'type' => $type, 'amount' => $amount, 'days' => $days, 'listing_id' => $item?->id,
            'verification_request_id' => $requestId, 'title' => $title, 'status' => 'paid',
            'ref_id' => (string) random_int(10000000, 99999999), 'paid_at' => now()->subDays($daysAgo), 'created_at' => now()->subDays($daysAgo),
        ]);
        $payment($parseh, 'verify', 150000, null, null, 'نشان تأیید کسب‌وکار', 31);
        $payment($parseh, 'featured', 290000, 30, $l['parseh_ig'], 'آگهی ویژه یک ماه — آموزشگاه زبان پارسه', 10);
        $payment($rose, 'verify', 150000, null, null, 'نشان تأیید کسب‌وکار', 21);
        $payment($mahoor, 'featured', 99000, 7, $l['mahoor_ig'], 'آگهی ویژه یک هفته — گالری ماهور', 2);
        $payment($rose, 'featured', 99000, 7, $l['rose_ig'], 'آگهی ویژه یک هفته — آرایشی رز', 5);
        $payment($ava, 'featured', 290000, 30, $l['ava_ig'], 'آگهی ویژه یک ماه — دکوراسیون آوا', 18);
        $payment($omid, 'verify', 150000, null, null, 'نشان تأیید کسب‌وکار', 1, $omidRequest->id);

        // ---------- گزارش تخلف ----------
        Report::create(['reporter_id' => $darchin->id, 'reportable_type' => Listing::class, 'reportable_id' => $l['omid_tg']->id, 'reason' => 'کلاهبرداری یا فروش جعلی', 'body' => 'پول واریز کردم ولی کالا ارسال نشد و دیگه جواب پیام‌ها رو نمیدن.', 'created_at' => now()->subDays(2)]);
        Report::create(['reporter_id' => $parvaz->id, 'reportable_type' => Listing::class, 'reportable_id' => $l['omid_tg']->id, 'reason' => 'کلاهبرداری یا فروش جعلی', 'body' => 'قیمت‌ها غیرواقعی و خیلی پایین‌تر از بازاره، مشکوکه.', 'created_at' => now()->subDay()]);
        Report::create(['reporter_id' => $mahoor->id, 'reportable_type' => Review::class, 'reportable_id' => $rudeReview->id, 'reason' => 'اطلاعات نادرست', 'body' => 'این کاربر هیچ‌وقت از ما خرید نکرده.', 'created_at' => now()->subDays(3)]);
        Report::create(['reporter_id' => $omid->id, 'reportable_type' => Listing::class, 'reportable_id' => $l['parvaz_ig']->id, 'reason' => 'اطلاعات یا لینک نادرست', 'body' => 'لینک پیج کار نمی‌کنه.', 'status' => 'resolved', 'note' => 'بررسی شد؛ لینک صحیح است و اقدامی لازم نبود.', 'created_at' => now()->subDays(9)]);

        // ---------- پیام‌های تماس ----------
        ContactMessage::create(['name' => 'سارا محمدی', 'contact' => '09121112233', 'subject' => 'همکاری', 'body' => 'سلام، برای تبلیغات ویژه پیجمان در صفحه اصلی سایت شرایطتون چیه؟', 'created_at' => now()->subDay()]);
        ContactMessage::create(['user_id' => $omid->id, 'name' => $omid->name, 'contact' => $omid->mobile, 'subject' => 'پشتیبانی', 'body' => 'پیج روبیکای ما تأیید شده ولی تعداد دنبال‌کننده‌ها رو می‌خوام آپدیت کنم، باید دوباره منتظر تأیید باشم؟', 'read_at' => now()->subDays(3), 'created_at' => now()->subDays(4)]);

        // ---------- خبرنامه و اعلان‌ها ----------
        Subscription::create(['user_id' => $mahoor->id, 'category_id' => $cat('cafe')]);
        Subscription::create(['user_id' => $arman->id, 'category_id' => $cat('education')]);
        Subscription::create(['contact' => '09351234567', 'category_id' => $cat('mobile')]);
        Subscription::create(['contact' => 'sara@example.com', 'category_id' => $cat('fashion')]);
        Subscription::create(['contact' => '09129876543']);
        Subscription::create(['contact' => 'ali@example.com']);

        $mahoor->notify(new SiteMessage('پیج جدید در «کافه و شیرینی»: '.$l['darchin_bale']->title, route('listings.show', $l['darchin_bale'])));
    }

    /** تصاویر نمونه گالری (SVG ساده با ایموجی) */
    private function seedGalleries(array $l): void
    {
        $galleries = [
            'mahoor_ig' => [['🧥', 'مانتو پاییزه', '#7c3aed', '#db2777'], ['👚', 'شومیز نخی', '#0ea5e9', '#6366f1'], ['👜', 'کیف دستی چرم', '#f59e0b', '#ef4444'], ['🧣', 'شال و روسری', '#10b981', '#0ea5e9'], ['👗', 'لباس مجلسی', '#ec4899', '#8b5cf6']],
            'darchin_ig' => [['☕', 'لاته مخصوص', '#92400e', '#f59e0b'], ['🍰', 'چیزکیک', '#f472b6', '#fb923c'], ['🥐', 'صبحانه کامل', '#eab308', '#f97316'], ['🌿', 'فضای کافه', '#16a34a', '#0d9488']],
            'omid_ig' => [['📱', 'گوشی‌های جدید', '#1e3a8a', '#7c3aed'], ['🎧', 'هدفون بی‌سیم', '#0f172a', '#0ea5e9'], ['⌚', 'ساعت هوشمند', '#334155', '#10b981']],
            'niloo_ig' => [['🏺', 'کوزه سفالی', '#b45309', '#ea580c'], ['🍵', 'فنجان دست‌ساز', '#0d9488', '#65a30d'], ['🍽️', 'بشقاب نقش‌دار', '#1d4ed8', '#0891b2']],
            'ava_ig' => [['🛋️', 'مبل راحتی', '#57534e', '#a16207'], ['🖼️', 'تابلو و آینه دکوری', '#0f766e', '#6d28d9'], ['💡', 'نورپردازی', '#ca8a04', '#ea580c']],
        ];
        // تصاویری که هنوز منتظر بررسی مدیر هستند
        $pending = [
            'parseh_ig' => [['📚', 'کلاس آیلتس', '#1d4ed8', '#7c3aed'], ['🎓', 'جشن فارغ‌التحصیلی', '#0f766e', '#2563eb']],
            'arman_ig' => [['🏋️', 'سالن بدنسازی', '#0f172a', '#dc2626'], ['🥗', 'برنامه غذایی', '#15803d', '#65a30d']],
        ];

        foreach (['approved' => $galleries, 'pending' => $pending] as $status => $set) {
            foreach ($set as $key => $photos) {
                foreach ($photos as $sort => [$emoji, $caption, $from, $to]) {
                    $l[$key]->photos()->create([
                        'path' => $this->sampleImage("photos/{$key}-{$status}-{$sort}.svg", $emoji, $from, $to),
                        'caption' => $caption,
                        'status' => $status,
                        'sort' => $sort,
                    ]);
                }
            }
        }
    }

    /**
     * آمار روزانه ۹۰ روز اخیر برای نمودارها.
     * عدد هر روز بر اساس دنبال‌کننده‌ها و با کمی نوسان ساخته می‌شود؛ روزهای ویژه بیشتر است.
     */
    private function seedDailyStats(): void
    {
        foreach (Listing::approved()->get() as $item) {
            $rows = [];
            $totalViews = $totalClicks = 0;

            for ($day = 89; $day >= 0; $day--) {
                $date = now()->subDays($day);
                if ($date->lt($item->created_at->startOfDay())) {
                    continue;
                }

                $views = (int) round(($item->followers / 2500 + 4) * (0.55 + mt_rand(0, 90) / 100));
                if ($item->isFeatured() && $day < 7) {
                    $views = (int) round($views * 1.7);
                }
                $clicks = (int) round($views * (0.1 + mt_rand(0, 12) / 100));

                $rows[] = ['listing_id' => $item->id, 'date' => $date->toDateString(), 'views' => $views, 'clicks' => $clicks];
                $totalViews += $views;
                $totalClicks += $clicks;
            }

            ListingDailyStat::insert($rows);
            $item->update(['views' => $totalViews, 'clicks' => $totalClicks]);
        }
    }

    /** یک تصویر SVG نمونه در دیسک public می‌سازد و مسیرش را برمی‌گرداند */
    private function sampleImage(string $path, string $emoji, string $from, string $to): string
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{$from}"/><stop offset="1" stop-color="{$to}"/></linearGradient></defs><rect width="800" height="600" fill="url(#g)"/><circle cx="640" cy="110" r="160" fill="#fff" opacity=".12"/><circle cx="120" cy="520" r="200" fill="#fff" opacity=".1"/><text x="400" y="380" font-size="240" text-anchor="middle">{$emoji}</text></svg>
        SVG;

        Storage::disk('public')->put("samples/{$path}", $svg);

        return "samples/{$path}";
    }
}
