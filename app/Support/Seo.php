<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use RalphJSmit\Laravel\SEO\SchemaCollection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * نگهدارنده داده سئوی صفحه فعلی.
 *
 * کامپوننت هر صفحه در render() داده را تنظیم می‌کند:
 *     Seo::set(new SEOData(title: '...', description: '...'));
 * و قالب اصلی (layouts/app) با Seo::render() تگ‌ها را با پکیج laravel-seo چاپ می‌کند.
 * چون کامپوننت Livewire قبل از قالب رندر می‌شود، داده همیشه آماده است.
 */
class Seo
{
    /** صفحه‌هایی که نباید در گوگل ایندکس شوند (پنل، داشبورد، ورود و پرداخت) */
    private const PRIVATE_ROUTES = [
        'dashboard', 'dashboard.*', 'admin.*', 'login', 'register', 'password.request', 'checkout', 'receipts.*',
    ];

    private ?SEOData $data = null;

    public static function set(SEOData $data): SEOData
    {
        return app(self::class)->data = $data;
    }

    public static function get(): ?SEOData
    {
        return app(self::class)->data;
    }

    /**
     * @param  string|null  $fallbackTitle  عنوانی که Livewire با #[Title] داده (اگر صفحه داده سئو نساخته باشد)
     */
    public static function render(?string $fallbackTitle = null): string
    {
        $data = self::get() ?? new SEOData;

        // صفحه اصلی عنوانش را از config/seo.php می‌گیرد
        if (! $data->title && ! request()->routeIs('home')) {
            $data->title = $fallbackTitle;
        }
        $data->locale = 'fa_IR';

        if (request()->routeIs(...self::PRIVATE_ROUTES) || app('router')->getCurrentRoute() === null) {
            $data->markAsNoindex();
        }

        return seo($data)->render();
    }

    /**
     * اگر صفحه با آدرس قدیمی یا نامک اشتباه باز شده (مثلاً /l/12 یا بعد از تغییر نام پیج)،
     * با ریدایرکت دائمی ۳۰۱ به آدرس اصلی می‌رود تا گوگل فقط یک آدرس را ایندکس کند.
     */
    public static function redirectToCanonical(Model $model, string $routeName, string $parameter): void
    {
        $requested = request()->route()?->originalParameter($parameter);

        if ($requested !== null && ! $model->isCanonicalKey($requested)) {
            throw new HttpResponseException(redirect()->route($routeName, $model, 301));
        }
    }

    /** ساخت JSON-LD مسیر صفحه (BreadcrumbList) از آرایه [نام => آدرس] */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->keys()->map(fn (string $name, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $name,
                'item' => $items[$name],
            ])->all(),
        ];
    }

    /** مجموعه schema از چند آرایه JSON-LD */
    public static function schemas(array ...$schemas): SchemaCollection
    {
        return SchemaCollection::initialize()->push(...$schemas);
    }

    /** خلاصه متن برای description (بدون خط جدید، حداکثر ۱۶۰ کاراکتر) */
    public static function excerpt(?string $text, int $limit = 160): string
    {
        return str((string) $text)->squish()->limit($limit, '…')->toString();
    }
}
