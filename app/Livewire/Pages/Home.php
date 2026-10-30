<?php

namespace App\Livewire\Pages;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Platform;
use App\Models\Review;
use App\Models\User;
use App\Support\Seo;
use Livewire\Component;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class Home extends Component
{
    public string $q = '';

    public function mount(): void
    {
        // عنوان صفحه اول از config/seo.php (homepage_title) می‌آید
        Seo::set(new SEOData(
            schema: Seo::schemas(
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => config('site.name'),
                    'url' => route('home'),
                    'inLanguage' => 'fa-IR',
                    // جعبه جستجوی سایت در نتایج گوگل
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('explore').'?q={search_term_string}'],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => config('site.name'),
                    'url' => route('home'),
                    'logo' => url('apple-touch-icon.png'),
                    'email' => config('site.contact.email'),
                    'telephone' => config('site.contact.phone'),
                ],
            ),
        ));
    }

    // جستجوی هدر صفحه اصلی → صفحه همه پیج‌ها با همان عبارت
    public function search()
    {
        return $this->redirectRoute('explore', ['q' => trim($this->q) ?: null], navigate: true);
    }

    public function render()
    {
        // تعداد پیج هر دسته (با زیردسته‌ها) با یک کوئری
        $perCategory = Listing::approved()->selectRaw('category_id, count(*) as total')->groupBy('category_id')->pluck('total', 'category_id');
        $countFor = fn (Category $category) => collect($category->selfAndDescendantIds())->sum(fn ($id) => $perCategory[$id] ?? 0);

        return view('livewire.pages.home', [
            'platforms' => Platform::active()->withCount(['listings' => fn ($q) => $q->approved()])->get(),
            'featured' => Listing::approved()->featured()->forCards()->inRandomOrder()->limit(8)->get(),
            'latest' => Listing::approved()->forCards()->latest()->limit(8)->get(),
            'categories' => Category::roots()->map(fn (Category $c) => ['category' => $c, 'count' => $countFor($c)]),
            'reviews' => Review::approved()->with(['user', 'listing'])->latest()->limit(3)->get(),
            'totals' => [
                'listings' => $perCategory->sum(),
                'businesses' => User::has('listings')->count(),
                'platforms' => Platform::active()->count(),
            ],
        ])->title('ویترین پیج و کانال کسب‌وکارها');
    }
}
