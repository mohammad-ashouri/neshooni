<div>
    {{-- ===== هیرو و جستجو ===== --}}
    <section class="relative overflow-hidden bg-linear-to-b from-violet-50 to-page">
        <div class="dot-grid pointer-events-none absolute inset-0"></div>
        <div class="blob pointer-events-none absolute -top-24 -left-24 size-96 rounded-full bg-fuchsia-300/30 blur-3xl"></div>
        <div class="blob blob-slow pointer-events-none absolute -right-24 top-20 size-80 rounded-full bg-violet-300/30 blur-3xl"></div>
        <div class="blob pointer-events-none absolute bottom-0 left-1/3 size-64 rounded-full bg-sky-300/20 blur-3xl [animation-delay:-6s]"></div>

        <div class="relative isolate mx-auto max-w-4xl px-4 py-16 text-center sm:py-24">
            <span class="chip float bg-surface text-violet-700 shadow-sm shadow-violet-500/10 ring-1 ring-violet-200">✨ <span x-data="countUp({{ (int) $totals['listings'] }})">{{ fa_number($totals['listings']) }}</span> پیج، کانال و سایت فروشگاهی فعال</span>
            <h1 class="mt-5 text-3xl leading-tight font-black text-slate-900 sm:text-5xl">
                پیج‌ها، کانال‌ها و سایت‌های فروشگاهی را
                <span class="text-shimmer bg-linear-to-l from-violet-600 via-fuchsia-500 to-violet-600 bg-clip-text text-transparent">یک‌جا پیدا کنید</span>
            </h1>
            <p class="mx-auto mt-4 max-w-2xl leading-8 text-slate-600">
                از اینستاگرام و تلگرام تا ایتا، روبیکا، بله و سایت‌های فروشگاهی؛ فروشگاه‌ها و خدمات مورد اعتماد را بر اساس دسته، شهر و امتیاز کاربران ببینید.
            </p>

            <form wire:submit="search" class="glow-ring mx-auto mt-8 flex max-w-xl gap-2 rounded-2xl border border-slate-200 bg-surface p-2 shadow-xl shadow-violet-500/10 transition duration-300 focus-within:shadow-2xl focus-within:shadow-violet-500/20">
                <input wire:model="q" class="min-w-0 flex-1 bg-transparent px-3 outline-none" placeholder="نام پیج یا سایت، آیدی، محصول یا شهر...">
                <button class="cursor-pointer btn-primary">جستجو</button>
            </form>

            <div class="stagger mt-6 flex flex-wrap justify-center gap-2">
                @foreach ($platforms as $platform)
                    <a href="{{ route('explore', ['platform' => $platform->key]) }}" wire:navigate class="group cursor-pointer filter-chip flex items-center gap-1.5 transition duration-200 hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-md hover:shadow-violet-500/10">
                        <x-platform-icon :platform="$platform" class="size-5 text-sm transition duration-300 group-hover:scale-125 group-hover:-rotate-6" />
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
            <section class="reveal">
                <div class="mb-5 flex items-end justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold">⭐ آگهی‌های ویژه</h2>
                        <p class="mt-1 text-xs text-slate-500">این پیج‌ها و سایت‌ها برای نمایش در این بخش هزینه پرداخت کرده‌اند.</p>
                    </div>
                    <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" wire:navigate class="cursor-pointer text-sm text-violet-700 hover:underline">پیج یا سایت شما هم اینجا باشد؟</a>
                </div>
                <div class="stagger grid gap-4 pt-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featured as $listing)
                        <x-listing-card :listing="$listing" ad />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== دسته‌بندی‌ها ===== --}}
        <section class="reveal">
            <div class="mb-5 flex items-end justify-between">
                <h2 class="text-xl font-extrabold">دسته‌بندی‌ها</h2>
                <a href="{{ route('explore') }}" wire:navigate class="cursor-pointer text-sm text-violet-700 hover:underline">همه پیج‌ها ←</a>
            </div>
            <div class="stagger grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($categories as ['category' => $category, 'count' => $count])
                    <a href="{{ $category->url() }}" wire:navigate class="spotlight cursor-pointer card group p-4 transition duration-300 hover:-translate-y-1 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-500/10">
                        <div class="wiggle inline-block text-3xl transition">{{ $category->icon }}</div>
                        <div class="mt-2 font-bold group-hover:text-violet-700">{{ $category->name }}</div>
                        <div class="mt-0.5 truncate text-xs text-slate-400">{{ $category->subs()->take(3)->pluck('name')->implode('، ') ?: '—' }}</div>
                        <div class="mt-2 text-xs text-slate-500">{{ fa_number($count) }} پیج</div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ===== تبلیغ ثبت سایت فروشگاهی ===== --}}
        <x-website-promo class="reveal-scale" />

        {{-- ===== جدیدترین پیج‌ها ===== --}}
        <section class="reveal">
            <div class="mb-5 flex items-end justify-between">
                <h2 class="text-xl font-extrabold">🆕 تازه‌ترین پیج‌ها و سایت‌ها</h2>
                <a href="{{ route('explore') }}" wire:navigate class="cursor-pointer text-sm text-violet-700 hover:underline">بیشتر ←</a>
            </div>
            <div class="stagger grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($latest as $listing)
                    <x-listing-card :listing="$listing" />
                @endforeach
            </div>
        </section>

        {{-- ===== آخرین نظرات ===== --}}
        @if ($reviews->isNotEmpty())
            <section class="reveal">
                <h2 class="mb-5 text-xl font-extrabold">💬 تازه‌ترین نظرات کاربران</h2>
                <div class="reveal-stagger grid gap-4 md:grid-cols-3">
                    @foreach ($reviews as $review)
                        <a href="{{ route('listings.show', $review->listing) }}" wire:navigate class="spotlight cursor-pointer card block p-5 transition duration-300 hover:-translate-y-1 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-500/10">
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
        <section class="reveal-scale card relative overflow-hidden p-8">
            <h2 class="text-center text-xl font-extrabold">در سه قدم دیده شوید</h2>
            <div class="relative mt-8 grid gap-6 text-center md:grid-cols-3">
                {{-- خط‌چین بین سه قدم --}}
                <div class="pointer-events-none absolute inset-x-[16%] top-7 hidden border-t-2 border-dashed border-violet-200 md:block"></div>
                @foreach ([['📝', 'ثبت‌نام رایگان', 'با شماره موبایل در کمتر از یک دقیقه حساب بسازید.'], ['🔗', 'معرفی پیج یا سایت', 'آیدی پیج و کانال یا آدرس سایت فروشگاهی، دسته، شهر و ساعات کاری را وارد کنید.'], ['🚀', 'دیده شوید', 'بعد از تأیید مدیر، پیج یا سایت شما به مشتریان نمایش داده می‌شود.']] as $i => [$icon, $title, $text])
                    <div class="group relative">
                        <div class="float mx-auto grid size-14 place-items-center rounded-2xl bg-violet-50 text-2xl ring-4 ring-surface transition duration-300 group-hover:scale-110 group-hover:bg-violet-100 group-hover:shadow-lg group-hover:shadow-violet-500/20" style="animation-delay: -{{ $i * 1.3 }}s">{{ $icon }}</div>
                        <h3 class="mt-3 font-bold">{{ fa_number($i + 1) }}. {{ $title }}</h3>
                        <p class="mt-1 text-sm leading-7 text-slate-500">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===== دعوت به ثبت ===== --}}
        <section class="reveal-scale bg-animated relative overflow-hidden rounded-3xl bg-linear-to-l from-violet-600 via-fuchsia-500 to-violet-600 px-6 py-12 text-center text-white">
            <div class="blob pointer-events-none absolute -top-16 -right-16 size-56 rounded-full bg-white/15 blur-2xl"></div>
            <div class="blob blob-slow pointer-events-none absolute -bottom-20 -left-10 size-64 rounded-full bg-fuchsia-300/25 blur-2xl"></div>
            <span class="float pointer-events-none absolute top-6 left-[12%] text-2xl opacity-70">✨</span>
            <span class="float float-delay pointer-events-none absolute right-[10%] bottom-8 text-xl opacity-60">🚀</span>
            <h2 class="relative text-2xl font-black">کسب‌وکار آنلاین دارید؟</h2>
            <p class="relative mt-2 text-white/80"><span x-data="countUp({{ (int) $totals['businesses'] }})">{{ fa_number($totals['businesses']) }}</span> کسب‌وکار در {{ fa_number($totals['platforms']) }} پلتفرم به نشونی اعتماد کرده‌اند. پیج دارید یا سایت فروشگاهی، جای شما اینجاست.</p>
            <div class="relative mt-6 flex flex-wrap justify-center gap-2">
                <a href="{{ auth()->check() ? route('dashboard.listings.create') : route('register') }}" wire:navigate class="shine cursor-pointer btn bg-white text-violet-600 shadow-lg shadow-violet-900/20 hover:-translate-y-0.5 hover:bg-violet-50">ثبت رایگان پیج</a>
                <a href="{{ auth()->check() ? route('dashboard.websites.create') : route('register', ['intent' => 'website']) }}" wire:navigate class="cursor-pointer btn bg-white/15 text-white ring-1 ring-white/40 hover:bg-white/25">🌐 ثبت رایگان سایت فروشگاهی</a>
            </div>
        </section>
    </div>
</div>
