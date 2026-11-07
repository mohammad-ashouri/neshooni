<?php

namespace App\Models;

use App\Enums\ModerationStatus;
use App\Models\Concerns\HasSlugInUrl;
use App\Models\Concerns\RecordsActivity;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * پیج یا کانال یک کسب‌وکار در یکی از پلتفرم‌ها.
 *
 * HasSEO: عنوان و توضیح دلخواه صاحب پیج برای گوگل در جدول seo (رابطه seo) ذخیره می‌شود.
 */
#[Fillable([
    'user_id', 'platform_id', 'category_id', 'handle', 'title', 'description', 'image', 'followers',
    'status', 'reject_reason', 'published_at', 'country', 'province', 'city', 'online_only', 'address', 'phone', 'hours',
    'views', 'clicks', 'featured_until',
])]
class Listing extends Model
{
    use HasSEO, HasSlugInUrl;
    use RecordsActivity;

    /** شمارنده‌ها با هر بازدید عوض می‌شوند و جایی در تاریخچه ندارند */
    protected function activityIgnored(): array
    {
        return ['views', 'clicks'];
    }

    /** روزهای هفته؛ ایندکس ۰ = شنبه */
    public const WEEK_DAYS = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'];

    protected function casts(): array
    {
        return [
            'status' => ModerationStatus::class,
            'online_only' => 'boolean',
            'hours' => 'array',
            'featured_until' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    // ---------- روابط ----------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ListingPhoto::class)->orderBy('sort')->orderBy('id');
    }

    /** فقط تصاویری که مدیر تأیید کرده (برای نمایش عمومی) */
    public function approvedPhotos(): HasMany
    {
        return $this->photos()->where('status', ModerationStatus::Approved);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', ModerationStatus::Approved);
    }

    /**
     * کاربرانی که پیج را در نشونی دنبال می‌کنند.
     * (ستون followers تعداد دنبال‌کننده در خود پلتفرم است؛ برای همین نام رابطه fans است)
     */
    public function fans(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows')->withTimestamps();
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(ListingDailyStat::class);
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    // ---------- اسکوپ‌ها ----------

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', ModerationStatus::Approved);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('featured_until', '>', now());
    }

    /** هرچه کارت پیج لازم دارد، یک‌جا (برای جلوگیری از کوئری‌های تکراری) */
    public function scopeForCards(Builder $query): void
    {
        $query->with(['platform', 'category', 'user'])
            ->withAvg('approvedReviews as rating', 'rating')
            ->withCount(['approvedReviews as reviews_count', 'approvedPhotos as photos_count']);
    }

    /** پیج‌های یک دسته و همه زیردسته‌هایش */
    public function scopeInCategory(Builder $query, Category $category): void
    {
        $query->whereIn('category_id', $category->selfAndDescendantIds());
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $term = '%'.str_replace(['ي', 'ك'], ['ی', 'ک'], trim($term)).'%';
        $query->where(fn (Builder $q) => $q
            ->where('title', 'like', $term)
            ->orWhere('handle', 'like', $term)
            ->orWhere('description', 'like', $term)
            ->orWhere('city', 'like', $term)
            ->orWhere('province', 'like', $term));
    }

    // ---------- نمایش ----------

    public function isFeatured(): bool
    {
        return $this->featured_until?->isFuture() ?? false;
    }

    /** آیا این مورد سایت فروشگاهی است (نه پیج شبکه اجتماعی)؟ */
    public function isWebsite(): bool
    {
        return $this->platform->usesUrl();
    }

    /** «پیج» یا «سایت»؛ برای متن‌هایی که بین هر دو مشترک است */
    public function kindLabel(): string
    {
        return $this->isWebsite() ? 'سایت' : 'پیج';
    }

    /** لینک پیج در خود پلتفرم (اینستاگرام، تلگرام و...) یا آدرس سایت فروشگاهی */
    public function externalUrl(): string
    {
        return $this->platform->profileUrl($this->handle);
    }

    /** لینک عمومی همین پیج در نشونی (برای اشتراک‌گذاری، QR و بنر) */
    public function publicUrl(): string
    {
        return rtrim(config('site.url'), '/').'/l/'.$this->getRouteKey();
    }

    protected function slugSource(): string
    {
        return $this->title;
    }

    public function displayHandle(): string
    {
        return match (true) {
            $this->platform->usesPhone() => '+'.$this->handle,
            $this->platform->usesUrl() => $this->handle,
            default => '@'.$this->handle,
        };
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function categoryLabel(bool $withIcon = true): string
    {
        if (! $this->category) {
            return 'بدون دسته';
        }

        $root = $this->category->root();

        return ($withIcon && $root->icon ? $root->icon.' ' : '').$this->category->pathLabel();
    }

    public function placeLabel(): string
    {
        if (! $this->city) {
            return '';
        }

        return $this->province && $this->province !== $this->city ? "{$this->city}، {$this->province}" : $this->city;
    }

    // ---------- سئو ----------

    /** عنوان خودکار برای گوگل: «گالری ماهور | مانتو در تهران» */
    public function autoSeoTitle(): string
    {
        $place = $this->online_only || ! $this->city ? '' : ' در '.$this->city;

        return Str::limit($this->title.' | '.($this->category?->name ?? $this->platform->label).$place, 60, '');
    }

    /** توضیح خودکار: خلاصه توضیحات پیج + پلتفرم و شهر */
    public function autoSeoDescription(): string
    {
        $suffix = " — {$this->platform->label} {$this->displayHandle()}".($this->city ? "، {$this->city}" : '');

        return Seo::excerpt($this->description, 160 - mb_strlen($suffix)).$suffix;
    }

    /**
     * داده کامل سئوی صفحه پیج برای پکیج laravel-seo.
     * عنوان و توضیحی که صاحب پیج در «ابزار سئو» نوشته (جدول seo) بر مقدار خودکار مقدم است.
     */
    public function seoData(): SEOData
    {
        $reviews = $this->approvedReviews()->get(['rating']);
        $categoryPath = $this->category?->path() ?? collect();

        $business = array_filter([
            '@context' => 'https://schema.org',
            // پیج با آدرس حضوری = کسب‌وکار محلی؛ فروش آنلاین = سازمان
            '@type' => $this->online_only || ! $this->address ? 'Organization' : 'LocalBusiness',
            'name' => $this->title,
            'description' => Seo::excerpt($this->description, 300),
            'url' => route('listings.show', $this),
            'image' => $this->imageUrl() ? url($this->imageUrl()) : null,
            'telephone' => $this->phone,
            'sameAs' => [$this->externalUrl()],
            'address' => $this->online_only || ! $this->city ? null : array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $this->address,
                'addressLocality' => $this->city,
                'addressRegion' => $this->province,
                'addressCountry' => $this->country,
            ]),
            'openingHoursSpecification' => $this->hasHours() ? $this->openingHoursSchema() : null,
            'aggregateRating' => $reviews->isEmpty() ? null : [
                '@type' => 'AggregateRating',
                'ratingValue' => round($reviews->avg('rating'), 1),
                'reviewCount' => $reviews->count(),
                'bestRating' => 5,
                'worstRating' => 1,
            ],
        ]);

        $crumbs = ['نشونی' => route('home')]
            + $categoryPath->mapWithKeys(fn (Category $c) => [$c->name => $c->url()])->all()
            + [$this->title => route('listings.show', $this)];

        $data = new SEOData(
            title: $this->seo->title ?: $this->autoSeoTitle(),
            description: $this->seo->description ?: $this->autoSeoDescription(),
            image: $this->imageUrl() ? ltrim($this->imageUrl(), '/') : null,
            url: route('listings.show', $this),
            published_time: $this->published_at,
            modified_time: $this->updated_at,
            schema: Seo::schemas($business, Seo::breadcrumbs($crumbs)),
            type: 'profile',
        );

        // پیج منتشرنشده (فقط صاحبش و ناظر می‌بینند) نباید ایندکس شود
        return $this->status === ModerationStatus::Approved ? $data : $data->markAsNoindex();
    }

    /** ساعات کاری به شکل schema.org (روزهای ما از شنبه شروع می‌شوند) */
    private function openingHoursSchema(): array
    {
        $englishDays = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        return collect($this->hours['days'])
            ->filter(fn (array $day) => ! empty($day['open']))
            ->map(fn (array $day, int $i) => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => 'https://schema.org/'.$englishDays[$i],
                'opens' => $day['from'],
                'closes' => $day['to'],
            ])
            ->values()
            ->all();
    }

    // ---------- ساعات کاری ----------

    public function hasHours(): bool
    {
        return ! empty($this->hours['enabled']);
    }

    /** متن ساعت یک روز: «۹:۰۰ تا ۲۱:۰۰»، «شبانه‌روزی» یا «تعطیل» */
    public static function dayHoursLabel(array $day): string
    {
        if (empty($day['open'])) {
            return 'تعطیل';
        }
        if ($day['from'] === '00:00' && $day['to'] === '23:59') {
            return 'شبانه‌روزی';
        }

        return fa_time($day['from']).' تا '.fa_time($day['to']);
    }

    /** ایندکس امروز در WEEK_DAYS (شنبه = ۰) */
    public static function todayIndex(): int
    {
        return (tehran_now()->dayOfWeek + 1) % 7;
    }

    /**
     * وضعیت لحظه‌ای: ['open' => bool, 'text' => '...'] یا null اگر ساعت کاری ثبت نشده.
     * ساعت پایان کمتر از شروع (مثل ۱۸ تا ۲) یعنی تا بامداد روز بعد.
     */
    public function hoursStatus(): ?array
    {
        if (! $this->hasHours()) {
            return null;
        }

        $days = $this->hours['days'];
        $today = static::todayIndex();
        $now = tehran_now();
        $minute = $now->hour * 60 + $now->minute;
        $toMinutes = fn (string $time) => (int) substr($time, 0, 2) * 60 + (int) substr($time, 3, 2);

        $current = $days[$today];
        $yesterday = $days[($today + 6) % 7];

        if (! empty($current['open']) && $current['from'] === '00:00' && $current['to'] === '23:59') {
            return ['open' => true, 'text' => 'شبانه‌روزی'];
        }

        // شیفتی که از دیروز شروع شده و از نیمه‌شب گذشته
        if (! empty($yesterday['open']) && $toMinutes($yesterday['to']) <= $toMinutes($yesterday['from']) && $minute < $toMinutes($yesterday['to'])) {
            return ['open' => true, 'text' => 'تا ساعت '.fa_time($yesterday['to'])];
        }

        if (! empty($current['open']) && $minute >= $toMinutes($current['from'])
            && ($toMinutes($current['to']) <= $toMinutes($current['from']) || $minute < $toMinutes($current['to']))) {
            return ['open' => true, 'text' => 'تا ساعت '.fa_time($current['to'])];
        }

        // نزدیک‌ترین زمان بازگشایی
        for ($i = 0; $i < 7; $i++) {
            $day = $days[($today + $i) % 7];
            if (empty($day['open']) || ($i === 0 && $minute >= $toMinutes($day['from']))) {
                continue;
            }
            $when = match ($i) {
                0 => 'امروز',
                1 => 'فردا',
                default => static::WEEK_DAYS[($today + $i) % 7],
            };

            return ['open' => false, 'text' => "باز می‌شود {$when} ساعت ".fa_time($day['from'])];
        }

        return ['open' => false, 'text' => 'تعطیل'];
    }

    /** ساختار پیش‌فرض ساعات کاری برای فرم */
    public static function defaultHours(): array
    {
        return [
            'enabled' => false,
            'days' => array_map(fn (int $i) => ['open' => $i !== 6, 'from' => '09:00', 'to' => '21:00'], range(0, 6)),
        ];
    }
}
