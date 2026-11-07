<div class="mx-auto max-w-7xl px-4 py-8">
    {{-- مسیر دسته --}}
    <nav class="mb-4 flex flex-wrap items-center gap-1.5 text-sm text-slate-500">
        <a href="{{ route('home') }}" wire:navigate class="cursor-pointer hover:text-violet-700">خانه</a>
        <span>›</span>
        <a href="{{ route('explore') }}" wire:navigate @class(['cursor-pointer hover:text-violet-700', 'font-semibold text-slate-800' => ! $current])>همه پیج‌ها</a>
        @foreach ($path as $node)
            <span>›</span>
            <a href="{{ $node->url() }}" wire:navigate @class(['cursor-pointer hover:text-violet-700', 'font-semibold text-slate-800' => $loop->last])>{{ $node->name }}</a>
        @endforeach
    </nav>

    <h1 class="text-2xl font-extrabold">{{ $current?->root()->icon }} {{ $heading }}</h1>

    {{-- متن معرفی دسته (از پنل مدیریت)؛ فقط در صفحه اول و بدون فیلتر، تا محتوای تکراری ساخته نشود --}}
    @if ($current?->intro && $listings->currentPage() === 1 && ! $platform && ! $province)
        <p class="mt-3 max-w-4xl leading-8 whitespace-pre-line text-slate-600">{{ $current->intro }}</p>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-[16rem_1fr]">
        {{-- ===== ستون دسته‌ها ===== --}}
        <aside class="card h-fit p-3 lg:sticky lg:top-20">
            <h2 class="px-3 py-2 text-sm font-bold text-slate-500">دسته‌بندی</h2>
            @if ($current)
                <a href="{{ $current->parent_id ? App\Models\Category::fromCache($current->parent_id)->url() : route('explore') }}" wire:navigate class="cursor-pointer side-link">→ {{ $current->parent_id ? App\Models\Category::fromCache($current->parent_id)->name : 'همه دسته‌ها' }}</a>
            @endif
            {{-- اگر دسته فعلی زیردسته ندارد، هم‌سطح‌هایش نمایش داده می‌شوند --}}
            @php($items = $subs->isEmpty() && $current?->parent_id ? App\Models\Category::fromCache($current->parent_id)->subs() : $subs)
            @foreach ($items as $item)
                <a href="{{ $item->url() }}" wire:navigate @class(['cursor-pointer side-link', 'side-link-active' => $current?->id === $item->id])>
                    @unless ($current) <span>{{ $item->icon }}</span> @endunless
                    <span class="flex-1">{{ $item->name }}</span>
                    @if ($item->subs()->isNotEmpty()) <span class="text-xs text-slate-400">‹</span> @endif
                </a>
            @endforeach
        </aside>

        <div class="min-w-0">
            {{-- ===== فیلترها ===== --}}
            <div class="card space-y-4 p-4">
                <div class="grid gap-3 md:grid-cols-[1fr_auto_auto_auto]">
                    <input wire:model.live.debounce.400ms="q" class="input" placeholder="🔍 جستجو در نام، آیدی، توضیحات یا شهر...">
                    <select wire:model.live="province" class="input md:w-40">
                        <option value="">همه استان‌ها</option>
                        @foreach ($provinces as $name)
                            <option>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="city" class="input md:w-36" @disabled(! $province)>
                        <option value="">همه شهرها</option>
                        @foreach ($cities as $name)
                            <option>{{ $name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="sort" class="input md:w-40">
                        <option value="new">جدیدترین</option>
                        <option value="rating">بیشترین امتیاز</option>
                        <option value="views">پربازدیدترین</option>
                        <option value="popular">محبوب‌ترین</option>
                        <option value="followers">بیشترین دنبال‌کننده</option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button wire:click="$set('platform', '')" @class(['cursor-pointer filter-chip', 'filter-chip-active' => ! $platform])>همه پلتفرم‌ها</button>
                    @foreach ($platforms as $item)
                        <button wire:click="$set('platform', '{{ $item->key }}')" @class(['cursor-pointer filter-chip flex items-center gap-1.5', 'filter-chip-active' => $platform === $item->key])>
                            <x-platform-icon :platform="$item" class="size-4 text-xs" /> {{ $item->label }}
                        </button>
                    @endforeach
                    <label class="filter-chip ms-auto flex cursor-pointer items-center gap-1.5">
                        <input type="checkbox" wire:model.live="open" class="accent-emerald-600"> الان باز است
                    </label>
                </div>
            </div>

            <div class="mt-5 mb-3 flex items-center justify-between text-sm text-slate-500">
                <span>{{ fa_number($listings->total()) }} نتیجه</span>
                <span wire:loading class="text-violet-600">در حال جستجو...</span>
            </div>

            @if ($ads->isNotEmpty())
                <section class="mb-6 rounded-2xl border border-amber-200 bg-amber-50/50 p-4" aria-label="آگهی‌های ویژه">
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span class="font-bold text-amber-700">آگهی ویژه</span>
                        <span class="text-slate-500">جایگاه تبلیغاتی؛ نتایج زیر مستقل از پرداخت مرتب شده‌اند</span>
                    </div>
                    <div class="grid gap-4 pt-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($ads as $listing)
                            <x-listing-card :listing="$listing" ad wire:key="ad-{{ $listing->id }}" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($listings->isEmpty())
                <x-empty icon="🔍" title="پیجی با این مشخصات پیدا نشد">
                    <button wire:click="resetFilters" class="cursor-pointer btn-light mt-3">پاک کردن فیلترها</button>
                </x-empty>
            @else
                <div class="grid gap-4 pt-2 sm:grid-cols-2 xl:grid-cols-3" wire:loading.class="opacity-60">
                    @foreach ($listings as $listing)
                        <x-listing-card :listing="$listing" wire:key="listing-{{ $listing->id }}" />
                    @endforeach
                </div>

                <x-page-links :paginator="$listings" :url="$pageUrl" class="mt-8" />
            @endif
        </div>
    </div>
</div>
