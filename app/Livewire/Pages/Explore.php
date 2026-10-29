<?php

namespace App\Livewire\Pages;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Platform;
use App\Services\InterestTracker;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * صفحه همه پیج‌ها با فیلتر.
 * دسته از آدرس صفحه خوانده می‌شود (/c/fashion/women) و بقیه فیلترها در query string هستند
 * تا لینک نتیجه جستجو قابل اشتراک‌گذاری باشد.
 */
class Explore extends Component
{
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

    public int $perPage = 12;

    public function mount(InterestTracker $tracker, ?string $category = null): void
    {
        if ($category) {
            $this->current = $this->findCategoryByPath($category) ?? abort(404);
            $tracker->add($this->current->id, 'category');
        }
    }

    // با تغییر هر فیلتر، تعداد نمایش به حالت اول برمی‌گردد
    public function updated(string $property): void
    {
        if ($property === 'province') {
            $this->city = '';
        }
        $this->perPage = 12;
    }

    public function loadMore(): void
    {
        $this->perPage += 12;
    }

    public function resetFilters(): void
    {
        $this->reset('q', 'platform', 'province', 'city', 'open', 'sort', 'perPage');
    }

    public function render()
    {
        $listings = $this->query()->get();

        // فیلتر «الان باز است» روی ساعات کاری JSON است و در PHP انجام می‌شود
        if ($this->open) {
            $listings = $listings->filter(fn (Listing $l) => $l->hoursStatus()['open'] ?? false)->values();
        }

        return view('livewire.pages.explore', [
            'listings' => $listings->take($this->perPage),
            'total' => $listings->count(),
            'platforms' => Platform::active()->get(),
            'provinces' => array_keys(config('locations.IR.provinces')),
            'cities' => config("locations.IR.provinces.{$this->province}", []),
            'path' => $this->current?->path() ?? collect(),
            'subs' => $this->current ? $this->current->subs() : Category::roots(),
        ])->title($this->current?->name ?? 'همه پیج‌ها');
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
            // پیج‌های ویژه همیشه اول
            ->orderByRaw('CASE WHEN featured_until > ? THEN 0 ELSE 1 END', [now()])
            ->when($this->sort === 'views', fn (Builder $q) => $q->orderByDesc('views'))
            ->when($this->sort === 'popular', fn (Builder $q) => $q->withCount('followers')->orderByDesc('followers_count'))
            ->when($this->sort === 'followers', fn (Builder $q) => $q->orderByDesc('followers'))
            ->latest();
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
