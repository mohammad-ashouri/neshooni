<div>
    {{-- ===== هیرو و جستجو ===== --}}
    <section class="relative overflow-hidden bg-linear-to-b from-violet-50 to-page">
        <div class="pointer-events-none absolute -top-24 -left-24 size-96 rounded-full bg-fuchsia-300/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 top-20 size-80 rounded-full bg-violet-300/30 blur-3xl"></div>

        <div class="relative mx-auto max-w-4xl px-4 py-16 text-center sm:py-24">
            <span class="chip bg-surface text-violet-700 ring-1 ring-violet-200">✨ {{ fa_number($totals['listings']) }} پیج و کانال فعال</span>
            <h1 class="mt-5 text-3xl leading-tight font-black text-slate-900 sm:text-5xl">
                پیج‌ها و کانال‌های کسب‌وکارها را
                <span class="bg-linear-to-l from-violet-600 to-fuchsia-500 bg-clip-text text-transparent">یک‌جا پیدا کنید</span>
            </h1>
            <p class="mx-auto mt-4 max-w-2xl leading-8 text-slate-600">
                از اینستاگرام و تلگرام تا ایتا، روبیکا و بله؛ فروشگاه‌ها و خدمات مورد اعتماد را بر اساس دسته، شهر و امتیاز کاربران ببینید.
            </p>

            <form wire:submit="search" class="mx-auto mt-8 flex max-w-xl gap-2 rounded-2xl border border-slate-200 bg-surface p-2 shadow-xl shadow-violet-500/10">
                <input wire:model="q" class="min-w-0 flex-1 bg-transparent px-3 outline-none" placeholder="نام پیج، آیدی، محصول یا شهر...">
                <button class="btn-primary">جستجو</button>
            </form>

            <div class="mt-6 flex flex-wrap justify-center gap-2">
                @foreach ($platforms as $platform)
                    <a href="{{ route('explore', ['platform' => $platform->key]) }}" wire:navigate class="filter-chip flex items-center gap-1.5">
                        <x-platform-icon :platform="$platform" class="size-5 text-sm" />
                        {{ $platform->label }}
                        <span class="text-slate-400">{{ fa_number($platform->listings_count) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl space-y-16 px-4 py-10">
        {{-- ===== پیج‌های ویژه ===== --}}
        @if ($featured->isNotEmpty())
            <section>
                <div class="mb-5 flex items-end justify-between">
                    <h2 class="text-xl font-extrabold">⭐ پیج‌های ویژه</h2>
                    <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" wire:navigate class="text-sm text-violet-700 hover:underline">پیج شما هم اینجا باشد؟</a>
                </div>
                <div class="stagger grid gap-4 pt-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featured as $listing)
                        <x-listing-card :listing="$listing" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== دسته‌بندی‌ها ===== --}}
        <section>
            <div class="mb-5 flex items-end justify-between">
                <h2 class="text-xl font-extrabold">دسته‌بندی‌ها</h2>
                <a href="{{ route('explore') }}" wire:navigate class="text-sm text-violet-700 hover:underline">همه پیج‌ها ←</a>
            </div>
            <div class="stagger grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($categories as ['category' => $category, 'count' => $count])
                    <a href="{{ $category->url() }}" wire:navigate class="card group p-4 transition hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-500/10">
                        <div class="text-3xl transition group-hover:scale-110">{{ $category->icon }}</div>
                        <div class="mt-2 font-bold group-hover:text-violet-700">{{ $category->name }}</div>
                        <div class="mt-0.5 truncate text-xs text-slate-400">{{ $category->subs()->take(3)->pluck('name')->implode('، ') ?: '—' }}</div>
                        <div class="mt-2 text-xs text-slate-500">{{ fa_number($count) }} پیج</div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ===== جدیدترین پیج‌ها ===== --}}
        <section>
            <div class="mb-5 flex items-end justify-between">
                <h2 class="text-xl font-extrabold">🆕 تازه‌ترین پیج‌ها</h2>
                <a href="{{ route('explore') }}" wire:navigate class="text-sm text-violet-700 hover:underline">بیشتر ←</a>
            </div>
            <div class="stagger grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($latest as $listing)
                    <x-listing-card :listing="$listing" />
                @endforeach
            </div>
        </section>

        {{-- ===== آخرین نظرات ===== --}}
        @if ($reviews->isNotEmpty())
            <section>
                <h2 class="mb-5 text-xl font-extrabold">💬 تازه‌ترین نظرات کاربران</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($reviews as $review)
                        <a href="{{ route('listings.show', $review->listing) }}" wire:navigate class="card block p-5 transition hover:border-violet-200">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">{{ $review->user->name }}</span>
                                <x-stars :rating="$review->rating" class="text-sm" />
                            </div>
                            <p class="mt-3 line-clamp-3 text-sm leading-7 text-slate-600">{{ $review->body }}</p>
                            <p class="mt-3 text-xs text-slate-400">درباره {{ $review->listing->title }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== چطور کار می‌کند ===== --}}
        <section class="card p-8">
            <h2 class="text-center text-xl font-extrabold">در سه قدم دیده شوید</h2>
            <div class="mt-8 grid gap-6 text-center md:grid-cols-3">
                @foreach ([['📝', 'ثبت‌نام رایگان', 'با شماره موبایل در کمتر از یک دقیقه حساب بسازید.'], ['🔗', 'معرفی پیج', 'آیدی پیج یا کانال، دسته، شهر و ساعات کاری را وارد کنید.'], ['🚀', 'دیده شوید', 'بعد از تأیید مدیر، پیج شما به مشتریان نمایش داده می‌شود.']] as $i => [$icon, $title, $text])
                    <div>
                        <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-violet-50 text-2xl">{{ $icon }}</div>
                        <h3 class="mt-3 font-bold">{{ fa_number($i + 1) }}. {{ $title }}</h3>
                        <p class="mt-1 text-sm leading-7 text-slate-500">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===== دعوت به ثبت ===== --}}
        <section class="relative overflow-hidden rounded-3xl bg-linear-to-l from-violet-600 to-fuchsia-500 px-6 py-12 text-center text-white">
            <h2 class="text-2xl font-black">کسب‌وکار آنلاین دارید؟</h2>
            <p class="mt-2 text-white/80">{{ fa_number($totals['businesses']) }} کسب‌وکار در {{ fa_number($totals['platforms']) }} پلتفرم به نشونی اعتماد کرده‌اند.</p>
            <a href="{{ auth()->check() ? route('dashboard.listings.create') : route('register') }}" wire:navigate class="btn mt-6 bg-white text-violet-600 hover:bg-violet-50">ثبت رایگان پیج</a>
        </section>
    </div>
</div>
