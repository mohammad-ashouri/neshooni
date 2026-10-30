<?php

namespace App\Services;

use App\Enums\ModerationStatus;
use App\Models\Listing;

/**
 * بررسی سئوی یک پیج برای صاحبش (ابزار «سئوی پیج» در داشبورد).
 *
 * هر مورد یک وزن دارد و جمع وزن‌ها ۱۰۰ است؛ امتیاز = جمع وزن موارد انجام‌شده.
 * برای اضافه کردن مورد جدید فقط یک آیتم به آرایه checks اضافه کنید (و وزن‌ها را طوری تنظیم کنید که جمع ۱۰۰ بماند).
 */
class ListingSeoAudit
{
    /**
     * @return array{score: int, checks: array<int, array{label: string, passed: bool, weight: int, tip: string}>, keywords: array<int, string>}
     */
    public function run(Listing $listing): array
    {
        $photos = $listing->photos()->where('status', ModerationStatus::Approved)->get(['caption']);
        $reviews = $listing->approvedReviews()->get(['reply']);
        $titleLength = mb_strlen($listing->title);

        $checks = [
            [
                'label' => 'نام پیج بین ۱۵ تا ۶۰ کاراکتر',
                'passed' => $titleLength >= 15 && $titleLength <= 60,
                'weight' => 10,
                'tip' => 'نام کامل و توصیفی بنویسید؛ مثلاً «گالری ماهور | مانتو و شومیز زنانه» به‌جای فقط «ماهور».',
            ],
            [
                'label' => 'توضیحات پیج حداقل ۱۵۰ کاراکتر',
                'passed' => mb_strlen($listing->description) >= 150,
                'weight' => 15,
                'tip' => 'بنویسید چه محصول یا خدمتی دارید، برای چه کسانی، در کدام شهر و چه مزیتی (ارسال رایگان، ضمانت و...).',
            ],
            [
                'label' => 'عنوان یا توضیح اختصاصی برای گوگل',
                'passed' => filled($listing->seo->title) || filled($listing->seo->description),
                'weight' => 10,
                'tip' => 'در همین صفحه، متنی که می‌خواهید در نتایج گوگل دیده شود را بنویسید.',
            ],
            [
                'label' => 'عکس پروفایل پیج',
                'passed' => filled($listing->image),
                'weight' => 10,
                'tip' => 'لوگو یا عکس پروفایل پیج را در فرم ویرایش بارگذاری کنید؛ در اشتراک‌گذاری لینک هم نمایش داده می‌شود.',
            ],
            [
                'label' => 'حداقل ۳ عکس تأییدشده در گالری',
                'passed' => $photos->count() >= 3,
                'weight' => 10,
                'tip' => 'عکس نمونه کار یا محصول، صفحه شما را کامل‌تر و برای گوگل تصاویر قابل جستجو می‌کند.',
            ],
            [
                'label' => 'همه عکس‌های گالری توضیح دارند',
                'passed' => $photos->isNotEmpty() && $photos->every(fn ($p) => filled($p->caption)),
                'weight' => 5,
                'tip' => 'توضیح هر عکس (مثل «مانتو کتان تابستانه») به‌عنوان متن جایگزین (alt) به گوگل می‌گوید عکس چیست.',
            ],
            [
                'label' => 'دسته‌بندی دقیق (آخرین سطح)',
                'passed' => $listing->category && $listing->category->subs()->isEmpty(),
                'weight' => 10,
                'tip' => 'دقیق‌ترین زیردسته را انتخاب کنید (مثلاً «مانتو» به‌جای «پوشاک»)؛ پیج شما در صفحه آن دسته هم دیده می‌شود.',
            ],
            [
                'label' => 'شهر یا «فقط فروش آنلاین» مشخص شده',
                'passed' => filled($listing->city) || $listing->online_only,
                'weight' => 5,
                'tip' => 'با مشخص کردن شهر، پیج شما در جستجوهای محلی مثل «مانتو تهران» هم دیده می‌شود.',
            ],
            [
                'label' => 'ساعات کاری ثبت شده',
                'passed' => $listing->hasHours(),
                'weight' => 5,
                'tip' => 'ساعات کاری در گوگل هم نمایش داده می‌شود و مشتری می‌داند کی جواب می‌دهید.',
            ],
            [
                'label' => 'تلفن یا آدرس تماس',
                'passed' => filled($listing->phone) || filled($listing->address),
                'weight' => 5,
                'tip' => 'اطلاعات تماس اعتماد مشتری و موتور جستجو را بیشتر می‌کند.',
            ],
            [
                'label' => 'حداقل ۳ نظر تأییدشده',
                'passed' => $reviews->count() >= 3,
                'weight' => 10,
                'tip' => 'از مشتری‌های راضی بخواهید در نشونی نظر بدهند؛ امتیاز ستاره‌ای در نتایج گوگل هم دیده می‌شود.',
            ],
            [
                'label' => 'پاسخ به همه نظرها',
                'passed' => $reviews->isNotEmpty() && $reviews->every(fn ($r) => filled($r->reply)),
                'weight' => 5,
                'tip' => 'پاسخ دادن به نظرها محتوای تازه به صفحه اضافه می‌کند و نشان می‌دهد پیج فعال است.',
            ],
        ];

        return [
            'score' => collect($checks)->where('passed', true)->sum('weight'),
            'checks' => $checks,
            'keywords' => $this->keywords($listing),
        ];
    }

    /** کلمات کلیدی پیشنهادی از روی دسته، شهر و پلتفرم */
    public function keywords(Listing $listing): array
    {
        $path = $listing->category?->path() ?? collect();
        $leaf = $path->last()?->name;
        $root = $path->first()?->name;
        $platform = $listing->platform->label;
        $city = $listing->online_only ? null : $listing->city;

        return collect([
            $leaf,
            $leaf && $city ? "{$leaf} {$city}" : null,
            $leaf ? "خرید {$leaf}" : null,
            $leaf ? "پیج {$leaf} در {$platform}" : null,
            $leaf && $listing->online_only ? "خرید آنلاین {$leaf}" : null,
            $root && $root !== $leaf ? $root : null,
            $city && $root ? "{$root} در {$city}" : null,
        ])->filter()->unique()->values()->all();
    }

    /** رنگ و برچسب امتیاز برای نمایش */
    public static function grade(int $score): array
    {
        return match (true) {
            $score >= 80 => ['label' => 'عالی', 'class' => 'bg-emerald-50 text-emerald-700', 'color' => '#10b981'],
            $score >= 50 => ['label' => 'متوسط', 'class' => 'bg-amber-50 text-amber-700', 'color' => '#f59e0b'],
            default => ['label' => 'ضعیف', 'class' => 'bg-rose-50 text-rose-700', 'color' => '#f43f5e'],
        };
    }
}
