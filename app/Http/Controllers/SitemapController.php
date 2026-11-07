<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * sitemap.xml و robots.txt برای موتورهای جستجو.
 * نقشه سایت یک ساعت کش می‌شود؛ با تأیید پیج جدید حداکثر یک ساعت بعد در نقشه می‌آید.
 */
class SitemapController extends Controller
{
    public function sitemap()
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), fn () => view('seo.sitemap', [
            'pages' => [
                ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
                ['loc' => route('explore'), 'priority' => '0.9', 'changefreq' => 'daily'],
                ['loc' => route('about'), 'priority' => '0.3', 'changefreq' => 'yearly'],
                ['loc' => route('contact'), 'priority' => '0.3', 'changefreq' => 'yearly'],
                ['loc' => route('help'), 'priority' => '0.3', 'changefreq' => 'monthly'],
            ],
            'categories' => Category::cached(),
            'listings' => Listing::approved()->latest('updated_at')->get(['id', 'title', 'image', 'updated_at']),
            // فقط کسب‌وکارهایی که حداقل یک پیج منتشرشده دارند
            'businesses' => User::whereHas('listings', fn ($q) => $q->approved())->get(['id', 'name', 'updated_at']),
        ])->render());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots()
    {
        // در محیط غیر production کل سایت از ایندکس خارج است تا نسخه تست در گوگل نیاید
        $lines = app()->isProduction()
            ? [
                'User-agent: *',
                'Disallow: /dashboard',
                'Disallow: /panel',
                'Disallow: /checkout',
                'Disallow: /receipts',
                'Disallow: /livewire',
                'Disallow: /captcha',
                'Disallow: /*?*q=',
                'Disallow: /*?*sort=',
                // وقتی سایت noindex است نقشه سایت معرفی نمی‌شود؛ خزیدن آزاد می‌ماند تا گوگل تگ noindex را ببیند
                ...(config('seo.noindex') ? [] : ['', 'Sitemap: '.url('sitemap.xml')]),
            ]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
