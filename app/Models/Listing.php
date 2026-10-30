<?php

namespace App\Models;

use App\Enums\ModerationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

/**
 * پیج یا کانال یک کسب‌وکار در یکی از پلتفرم‌ها.
 */
#[Fillable([
    'user_id', 'platform_id', 'category_id', 'handle', 'title', 'description', 'image', 'followers',
    'status', 'reject_reason', 'published_at', 'country', 'province', 'city', 'online_only', 'address', 'phone', 'hours',
    'views', 'clicks', 'featured_until',
])]
class Listing extends Model
{
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

    /** لینک پیج در خود پلتفرم (اینستاگرام، تلگرام و...) */
    public function externalUrl(): string
    {
        return $this->platform->profileUrl($this->handle);
    }

    /** لینک عمومی همین پیج در نشونی (برای اشتراک‌گذاری، QR و بنر) */
    public function publicUrl(): string
    {
        return rtrim(config('site.url'), '/').'/l/'.$this->id;
    }

    public function displayHandle(): string
    {
        return $this->platform->usesPhone() ? '+'.$this->handle : '@'.$this->handle;
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
