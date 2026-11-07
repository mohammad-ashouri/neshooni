<?php

namespace App\Livewire\Pages;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Platform;
use App\Services\InterestTracker;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * صفحه همه پیج‌ها با فیلتر.
 * دسته از آدرس صفحه خوانده می‌شود (/c/fashion/women) و بقیه فیلترها در query string هستند
 * تا لینک نتیجه جستجو قابل اشتراک‌گذاری باشد.
 *
 * سئو:
 * - صفحه‌بندی با لینک واقعی (?page=2) است تا گوگل همه پیج‌ها را ببیند.
 * - دسته، پلتفرم، استان و شهر صفحه قابل ایندکس می‌سازند («مانتو اینستاگرام در تهران»)؛
 *   جستجو، «الان باز است» و مرتب‌سازی noindex هستند تا صفحه تکراری ساخته نشود.
 */
class Explore extends Component
{
    public const PER_PAGE = 12;

    public ?Category $current = null;

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $platform = '';

    #[Url(except: '')]
    public string $province = '';

    #[Url(except: '')]
    public string $city = '';

    #[Url(except: false)]
    public bool $open = false;

    #[Url(except: 'new')]
    public string $sort = 'new';

    #[Url(except: 1)]
    public int $page = 1;

    public function mount(InterestTracker $tracker, ?string $category = null): void
    {
        if ($category) {
            $this->current = $this->findCategoryByPath($category) ?? abort(404);
            $tracker->add($this->current->id, 'category');
        }
        $this->page = max(1, $this->page);
    }

    // با تغییر هر فیلتر، صفحه‌بندی از اول شروع می‌شود
    public function updated(string $property): void
    {
        if ($property === 'province') {
            $this->city = '';
        }
        if ($property !== 'page') {
            $this->page = 1;
        }
    }

    public function resetFilters(): void
    {
        $this->reset('q', 'platform', 'province', 'city', 'open', 'sort', 'page');
    }

    public function render()
    {
        $listings = $this->paginate();
        abort_if($this->page > 1 && $listings->isEmpty(), 404);

        $platform = $this->platform ? Platform::where('key', $this->platform)->first() : null;
        $this->setSeo($listings, $platform);

        return view('livewire.pages.explore', [
            'listings' => $listings,
            'ads' => $this->page === 1 && ! $this->open ? $this->ads() : collect(),
            'heading' => $this->heading($platform),
            'pageUrl' => fn (int $page) => $this->pageUrl($page),
            'platforms' => Platform::active()->get(),
            'provinces' => array_keys(config('locations.IR.provinces')),
            'cities' => config("locations.IR.provinces.{$this->province}", []),
            'path' => $this->current?->path() ?? collect(),
            'subs' => $this->current ? $this->current->subs() : Category::roots(),
        ])->title($this->heading($platform));
    }

    private function paginate(): LengthAwarePaginator
    {
        // فیلتر «الان باز است» روی ساعات کاری JSON است و باید در PHP انجام شود
        if ($this->open) {
            $all = $this->query()->get()->filter(fn (Listing $l) => $l->hoursStatus()['open'] ?? false)->values();

            return new LengthAwarePaginator($all->forPage($this->page, self::PER_PAGE), $all->count(), self::PER_PAGE, $this->page);
        }

        return $this->query()->paginate(self::PER_PAGE, page: $this->page);
    }

    private function query(): Builder
    {
        return Listing::approved()
            ->forCards()
            ->when($this->current, fn (Builder $q) => $q->inCategory($this->current))
            ->when($this->q !== '', fn (Builder $q) => $q->search($this->q))
            ->when($this->platform, fn (Builder $q) => $q->whereRelation('platform', 'key', $this->platform))
            ->when($this->province, fn (Builder $q) => $q->where('province', $this->province))
            ->when($this->city, fn (Builder $q) => $q->where('city', $this->city))
            // رتبه‌بندی نتایج مستقل از پرداخت است؛ پیج‌های ویژه جدا در ردیف «آگهی ویژه» بالای نتایج می‌آیند
            ->when($this->sort === 'rating', fn (Builder $q) => $q->orderByRaw('rating DESC NULLS LAST')->orderByDesc('reviews_count'))
            ->when($this->sort === 'views', fn (Builder $q) => $q->orderByDesc('views'))
            ->when($this->sort === 'popular', fn (Builder $q) => $q->withCount('fans')->orderByDesc('fans_count'))
            ->when($this->sort === 'followers', fn (Builder $q) => $q->orderByDesc('followers'))
            ->latest()
            ->orderByDesc('id');
    }

    /**
     * حداکثر ۳ پیج ویژه که با همین فیلترها جور هستند، به ترتیب تصادفی.
     * جدا از نتایج عادی و با برچسب «آگهی ویژه» نمایش داده می‌شوند.
     */
    private function ads(): Collection
    {
        return $this->query()->reorder()->featured()->inRandomOrder()->limit(3)->get();
    }

    /** «مانتو در اینستاگرام در تهران» */
    private function heading(?Platform $platform): string
    {
        return ($this->current?->name ?? 'همه پیج‌ها، کانال‌ها و سایت‌های فروشگاهی')
            .($platform ? ' در '.$platform->label : '')
            .($this->city ? ' در '.$this->city : ($this->province ? ' در استان '.$this->province : ''));
    }

    /** آدرس یک صفحه با همان فیلترها (برای لینک صفحه‌بندی و canonical) */
    private function pageUrl(int $page, bool $onlyIndexable = false): string
    {
        $query = array_filter([
            'platform' => $this->platform,
            'province' => $this->province,
            'city' => $this->city,
        ] + ($onlyIndexable ? [] : [
            'q' => $this->q,
            'open' => $this->open ? 1 : null,
            'sort' => $this->sort === 'new' ? null : $this->sort,
        ]) + ['page' => $page > 1 ? $page : null]);

        $base = $this->current ? $this->current->url() : route('explore');

        return $query ? $base.'?'.http_build_query($query) : $base;
    }

    private function setSeo(LengthAwarePaginator $listings, ?Platform $platform): void
    {
        $hasFilters = $this->platform || $this->province || $this->city;
        $heading = $this->heading($platform);
        $pageSuffix = $this->page > 1 ? ' - صفحه '.fa_number($this->page) : '';

        // عنوان و توضیح دلخواه مدیر فقط برای صفحه اصلی دسته (بدون فیلتر)
        $title = (! $hasFilters ? $this->current?->seo_title : null) ?: $heading.' | معرفی و مقایسه';
        $description = (! $hasFilters ? $this->current?->seo_description : null)
            ?: fa_number($listings->total())." پیج، کانال و سایت فروشگاهی {$heading}؛ "
                .'مقایسه بر اساس امتیاز و نظر خریداران، ساعات کاری و آدرس، همراه با لینک مستقیم پیج یا سایت.';

        $crumbs = ['نشونی' => route('home'), 'همه پیج‌ها' => route('explore')]
            + ($this->current?->path()->mapWithKeys(fn (Category $c) => [$c->name => $c->url()])->all() ?? []);

        $data = Seo::set(new SEOData(
            title: $title.$pageSuffix,
            description: Seo::excerpt($description),
            url: $this->pageUrl($this->page),
            canonical_url: $this->pageUrl($this->page, onlyIndexable: true),
            schema: Seo::schemas(Seo::breadcrumbs($crumbs), [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => $heading,
                'numberOfItems' => $listings->total(),
                'itemListElement' => $listings->values()->map(fn (Listing $l, int $i) => [
                    '@type' => 'ListItem',
                    'position' => ($this->page - 1) * self::PER_PAGE + $i + 1,
                    'url' => route('listings.show', $l),
                    'name' => $l->title,
                ])->all(),
            ]),
        ));

        // جستجو، مرتب‌سازی، «الان باز» و صفحه بدون نتیجه ایندکس نشوند
        if ($this->q !== '' || $this->open || $this->sort !== 'new' || $listings->isEmpty()) {
            $data->robots = 'noindex, follow';
        }
    }

    /** «fashion/women/manteau» → دسته مانتو (هر بخش باید زیرمجموعه بخش قبلی باشد) */
    private function findCategoryByPath(string $path): ?Category
    {
        $parentId = null;
        $found = null;
        foreach (explode('/', trim($path, '/')) as $slug) {
            $found = Category::cached()->first(fn (Category $c) => $c->slug === $slug && $c->parent_id === $parentId);
            if (! $found) {
                return null;
            }
            $parentId = $found->id;
        }

        return $found;
    }
}
