<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection as BaseCollection;

/**
 * دسته‌بندی درختی تا سه سطح.
 *
 * چون تعداد دسته‌ها کم است، کل جدول یک‌بار در هر درخواست خوانده و در حافظه
 * نگه داشته می‌شود (cached()) و کارهای درختی روی همان انجام می‌شود.
 */
#[Fillable(['parent_id', 'name', 'slug', 'icon', 'intro', 'seo_title', 'seo_description', 'sort'])]
class Category extends Model
{
    public const MAX_DEPTH = 3;

    protected static ?Collection $cache = null;

    protected static function booted(): void
    {
        // بعد از هر تغییر، حافظه موقت پاک می‌شود
        static::saved(fn () => static::$cache = null);
        static::deleted(fn () => static::$cache = null);
    }

    /** همه دسته‌ها (یک‌بار در هر درخواست از دیتابیس) */
    public static function cached(): Collection
    {
        return static::$cache ??= static::query()->orderBy('sort')->orderBy('id')->get()->keyBy('id');
    }

    public static function fromCache(int|string|null $id): ?self
    {
        return $id ? static::cached()->get((int) $id) : null;
    }

    public static function roots(): Collection
    {
        return static::cached()->whereNull('parent_id')->values();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /** زیردسته‌های مستقیم (از حافظه) */
    public function subs(): Collection
    {
        return static::cached()->where('parent_id', $this->id)->values();
    }

    /** مسیر از ریشه تا خود دسته: [پوشاک، زنانه، مانتو] */
    public function path(): BaseCollection
    {
        $path = collect();
        for ($node = $this; $node; $node = static::fromCache($node->parent_id)) {
            $path->prepend($node);
        }

        return $path;
    }

    public function depth(): int
    {
        return $this->path()->count();
    }

    /** چند سطح زیر این دسته وجود دارد (برگ = ۰) */
    public function height(): int
    {
        $subs = $this->subs();

        return $subs->isEmpty() ? 0 : 1 + $subs->max(fn (self $sub) => $sub->height());
    }

    /** شناسه خود دسته و همه زیرمجموعه‌هایش (برای فیلتر پیج‌ها) */
    public function selfAndDescendantIds(): array
    {
        return [$this->id, ...$this->subs()->flatMap(fn (self $sub) => $sub->selfAndDescendantIds())];
    }

    public function root(): self
    {
        return $this->path()->first();
    }

    public function pathLabel(string $separator = ' › '): string
    {
        return $this->path()->pluck('name')->implode($separator);
    }

    /** آدرس صفحه دسته: /c/fashion/women/manteau */
    public function url(): string
    {
        return route('explore', ['category' => $this->path()->pluck('slug')->implode('/')]);
    }

    /** همه دسته‌ها به ترتیب درختی با سطح، برای لیست‌های کشویی و جدول مدیریت */
    public static function flatTree(): BaseCollection
    {
        $walk = function (Collection $nodes, int $depth) use (&$walk): array {
            return $nodes->flatMap(fn (self $c) => [['category' => $c, 'depth' => $depth], ...$walk($c->subs(), $depth + 1)])->all();
        };

        return collect($walk(static::roots(), 1));
    }
}
