<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * آدرس خوانا برای سئو: /l/12-گالری-ماهور به‌جای /l/12
 *
 * route('listings.show', $listing) خودکار نامک را اضافه می‌کند و هنگام خواندن آدرس
 * فقط عدد اول آن (شناسه) مهم است؛ پس اگر نام پیج عوض شود، لینک‌های قدیمی هم کار می‌کنند.
 * صفحه نمایش با isCanonicalKey() بررسی می‌کند و آدرس قدیمی را با 301 به آدرس درست می‌برد.
 */
trait HasSlugInUrl
{
    /** متنی که نامک از آن ساخته می‌شود */
    abstract protected function slugSource(): string;

    public function getRouteKey(): string
    {
        $slug = fa_slug($this->slugSource());

        return $slug === '' ? (string) $this->getKey() : $this->getKey().'-'.$slug;
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if ($field || ! preg_match('/^(\d+)/', (string) $value, $match)) {
            return parent::resolveRouteBinding($value, $field);
        }

        return $this->whereKey((int) $match[1])->first();
    }

    /** آیا آدرس درخواست‌شده همان آدرس اصلی است؟ */
    public function isCanonicalKey(string $value): bool
    {
        return rawurldecode($value) === $this->getRouteKey();
    }
}
