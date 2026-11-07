<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * تا وقتی SEO_NOINDEX روشن است، روی همه پاسخ‌ها (صفحه، فایل دانلودی، PDF، XML، درخواست‌های Livewire و ...)
 * هدر X-Robots-Tag می‌گذارد تا هیچ آدرسی از سایت در موتورهای جستجو نیاید.
 * فایل‌های استاتیک پوشه public از Laravel رد نمی‌شوند؛ آن‌ها در public/.htaccess پوشش داده شده‌اند.
 */
class NoIndex
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (config('seo.noindex')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet, noimageindex');
        }

        return $response;
    }
}
