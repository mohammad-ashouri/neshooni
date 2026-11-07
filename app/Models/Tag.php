<?php

namespace App\Models;

use App\Enums\ModerationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Tags\Tag as SpatieTag;

/**
 * برچسب پیج؛ مثل «کافه»، «تهرانسر» یا «ارسال رایگان» (بر پایه spatie/laravel-tags).
 * نام برچسب ترجمه‌پذیر است و با زبان فعلی برنامه (fa) در ستون JSON ذخیره می‌شود.
 * هر پیج حداکثر MAX_PER_LISTING برچسب دارد و جستجو برچسب‌ها را هم می‌گردد.
 */
class Tag extends SpatieTag
{
    public const MAX_PER_LISTING = 10;

    public const MAX_LENGTH = 30;

    public function listings(): MorphToMany
    {
        return $this->morphedByMany(Listing::class, config('tags.taggable.morph_name'), config('tags.taggable.table_name'));
    }

    /**
     * اسلاگ پیش‌فرض پکیج حروف فارسی را حذف یا لاتین می‌کند و ممکن است دو برچسب اسلاگ یکسان بگیرند
     * (findOrCreate با اسلاگ هم جستجو می‌کند)؛ پس حروف فارسی در اسلاگ می‌مانند: «ارسال رایگان» → «ارسال-رایگان»
     */
    protected function generateSlug(string $locale): string
    {
        return Str::slug($this->getTranslation('name', $locale), '-', null);
    }

    /** ستون JSON نام برچسب در زبان فعلی؛ برای where روی نام */
    public static function nameColumn(): string
    {
        return 'name->'.static::getLocale();
    }

    /**
     * «#کافه_دنج » → «کافه دنج»: بدون #، ی و ک فارسی، فاصله‌های یکدست.
     * خروجی خالی یعنی برچسب معتبر نیست.
     */
    public static function normalize(string $name): string
    {
        $name = str_replace(['ي', 'ك', '_', '#', '،', ','], ['ی', 'ک', ' ', ' ', ' ', ' '], $name);
        $name = trim(preg_replace('/\s+/u', ' ', $name));

        return mb_substr($name, 0, self::MAX_LENGTH);
    }

    /** متن واردشده (جداشده با ویرگول، «،» یا #) → فهرست برچسب‌های تمیز */
    public static function parse(string $input): array
    {
        return collect(preg_split('/[,،#\n]+/u', $input))
            ->map(fn (string $part) => static::normalize($part))
            ->filter(fn (string $name) => mb_strlen($name) >= 2)
            ->unique()
            ->values()
            ->all();
    }

    /** پرکاربردترین برچسب‌های پیج‌های منتشرشده (برای پیشنهاد در فرم و صفحه جستجو) */
    public static function popular(int $limit = 12): Collection
    {
        $approved = fn (Builder $q) => $q->where('status', ModerationStatus::Approved);

        return static::query()
            ->whereHas('listings', $approved)
            ->withCount(['listings' => $approved])
            ->orderByDesc('listings_count')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->pluck('name')
            ->values();
    }
}
