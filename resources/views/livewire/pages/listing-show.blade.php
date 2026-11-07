@php
    $url = $listing->publicUrl();
    $shareText = $listing->title.' در نشونی';
    $mapQuery = urlencode(trim(($listing->address ?? '').' '.$listing->city));
@endphp

<div class="mx-auto max-w-6xl px-4 py-8">
    {{-- مسیر --}}
    <nav class="mb-5 flex flex-wrap items-center gap-1.5 text-sm text-slate-500">
        <a href="{{ route('home') }}" wire:navigate class="cursor-pointer hover:text-violet-700">خانه</a>
        @foreach ($listing->category?->path() ?? [] as $node)
            <span>›</span>
            <a href="{{ $node->url() }}" wire:navigate class="cursor-pointer hover:text-violet-700">{{ $node->name }}</a>
        @endforeach
        <span>›</span>
        <span class="font-semibold text-slate-800">{{ $listing->title }}</span>
    </nav>

    {{-- پیام برای صاحب پیج یا ناظر وقتی پیج هنوز منتشر نشده --}}
    @if ($listing->status !== App\Enums\ModerationStatus::Approved)
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            این پیج «{{ $listing->status->label() }}» است و فعلاً فقط شما آن را می‌بینید.
            @if ($listing->reject_reason) دلیل: {{ $listing->reject_reason }} @endif
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="min-w-0 space-y-6">
            {{-- ===== سربرگ پیج ===== --}}
            <section @class(['card p-6', 'border-amber-300 ring-1 ring-amber-200' => $listing->isFeatured()])>
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                    <div class="relative self-start">
                        <x-avatar :src="$listing->imageUrl()" :name="$listing->title" :seed="$listing->id" class="size-24 text-4xl" />
                        <span class="absolute -bottom-2 -left-2 rounded-lg ring-4 ring-surface"><x-platform-icon :platform="$listing->platform" class="size-8 text-lg" /></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-extrabold">{{ $listing->title }}</h1>
                            @if ($listing->user->isVerified()) <x-verified-badge class="size-5" /> @endif
                            @if ($listing->isFeatured()) <span class="chip bg-amber-100 text-amber-700" title="صاحب پیج برای نمایش ویژه هزینه پرداخت کرده">آگهی ویژه</span> @endif
                        </div>
                        <p class="mt-1 text-right text-slate-500" dir="ltr">{{ $listing->displayHandle() }}</p>
                        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
                            <span>{{ $listing->platform->label }}</span>
                            @if ($listing->followers) <span>👥 {{ fa_compact($listing->followers) }} دنبال‌کننده در {{ $listing->platform->label }}</span> @endif
                            <span>❤️ {{ fa_number($followersCount) }} نفر در نشونی</span>
                            <span>👁️ {{ fa_number($listing->views) }} بازدید</span>
                            @if ($reviews->isNotEmpty()) <span class="text-amber-600">★ {{ fa_number($average, 1) }} ({{ fa_number($reviews->count()) }} نظر)</span> @endif
                        </div>
                    </div>
                </div>

                <p class="mt-5 leading-8 whitespace-pre-line text-slate-700">{{ $listing->description }}</p>

                <div class="mt-6 flex flex-wrap gap-2">
                    <button wire:click="visit" class="cursor-pointer btn-primary">{{ $listing->isWebsite() ? 'ورود به سایت' : 'مشاهده در '.$listing->platform->label }} ↗</button>
                    @unless ($this->isOwner)
                        <button wire:click="toggleFollow" @class(['cursor-pointer btn', 'bg-rose-50 text-rose-700 hover:bg-rose-100' => $this->isFollowing, 'btn-light' => ! $this->isFollowing])>
                            {{ $this->isFollowing ? '💔 لغو دنبال کردن' : '❤️ دنبال کردن' }}
                        </button>
                    @endunless
                    <button wire:click="$set('showShare', true)" class="cursor-pointer btn-light">🔗 اشتراک‌گذاری</button>
                    @if ($this->isOwner)
                        <a href="{{ route('dashboard.listings.edit', $listing) }}" wire:navigate class="cursor-pointer btn-light">✏️ ویرایش</a>
                        <a href="{{ route('dashboard.banner', $listing) }}" wire:navigate class="cursor-pointer btn-light">🖼️ بنر «ما در نشونی هستیم»</a>
                    @else
                        <button wire:click="openReport('listing')" class="cursor-pointer btn text-slate-500 hover:bg-slate-100">🚩 گزارش</button>
                    @endif
                </div>
            </section>

            {{-- ===== گالری ===== --}}
            @if ($this->photos->isNotEmpty())
                <section class="card p-6">
                    <h2 class="mb-4 font-bold">🖼️ نمونه کارها و محصولات</h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($this->photos as $i => $item)
                            <button wire:click="showPhoto({{ $i }})" class="cursor-pointer group relative aspect-square overflow-hidden rounded-xl bg-slate-100">
                                <img src="{{ $item->url() }}" alt="{{ $item->caption ?: $listing->title }}" class="size-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                                @if ($item->caption)
                                    <span class="absolute inset-x-0 bottom-0 truncate bg-linear-to-t from-black/70 px-2 pt-6 pb-1.5 text-xs text-white">{{ $item->caption }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ===== نظرات ===== --}}
            <section class="card p-6">
                <h2 class="font-bold">💬 نظرات کاربران</h2>

                @if ($reviews->isNotEmpty())
                    <div class="mt-5 grid items-center gap-6 sm:grid-cols-[10rem_1fr]">
                        <div class="text-center">
                            <div class="text-4xl font-black">{{ fa_number($average, 1) }}</div>
                            <x-stars :rating="$average" class="text-lg" />
                            <div class="text-xs text-slate-500">از {{ fa_number($reviews->count()) }} نظر</div>
                        </div>
                        <div class="space-y-1.5">
                            @foreach ($distribution as $star => $count)
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="w-6 text-slate-500">{{ fa_number($star) }}★</span>
                                    <div class="h-2 flex-1 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-amber-400" style="width: {{ $count / $reviews->count() * 100 }}%"></div></div>
                                    <span class="w-6 text-slate-400">{{ fa_number($count) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- فرم نظر --}}
                @if (! $this->isOwner)
                    <div class="mt-6 rounded-xl bg-slate-50 p-4">
                        @auth
                            @if ($this->myReview?->status === App\Enums\ModerationStatus::Pending)
                                <p class="mb-3 text-sm text-amber-700">⏳ نظر شما در انتظار تأیید مدیر است. می‌توانید آن را ویرایش کنید.</p>
                            @endif
                            <form wire:submit="submitReview" class="space-y-3">
                                <div class="flex items-center gap-1 text-2xl">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <button type="button" wire:click="$set('rating', {{ $i }})" @class(['cursor-pointer transition hover:scale-110', 'text-amber-400' => $rating >= $i, 'text-slate-300' => $rating < $i]) aria-label="{{ fa_number($i) }} ستاره">★</button>
                                    @endfor
                                </div>
                                @error('rating') <p class="error">{{ $message }}</p> @enderror
                                <textarea wire:model="body" rows="3" class="input" placeholder="تجربه خود از خرید یا خدمات این کسب‌وکار را بنویسید..."></textarea>
                                @error('body') <p class="error">{{ $message }}</p> @enderror
                                <button class="cursor-pointer btn-primary">{{ $this->myReview ? 'ویرایش نظر' : 'ثبت نظر' }}</button>
                            </form>
                        @else
                            <p class="text-sm text-slate-600">برای ثبت نظر <a href="{{ route('login') }}" wire:navigate class="cursor-pointer font-semibold text-violet-700 hover:underline">وارد شوید</a>.</p>
                        @endauth
                    </div>
                @endif

                <div class="mt-6 divide-y divide-slate-100">
                    @forelse ($reviews as $review)
                        <article class="py-4" wire:key="review-{{ $review->id }}">
                            <div class="flex items-center gap-3">
                                <x-avatar :src="$review->user->avatarUrl()" :name="$review->user->name" :seed="$review->user_id" class="size-9 text-sm" rounded="rounded-full" />
                                <div class="flex-1">
                                    <div class="text-sm font-semibold">{{ $review->user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ fa_date($review->created_at) }}</div>
                                </div>
                                <x-stars :rating="$review->rating" class="text-sm" />
                            </div>
                            <p class="mt-2 text-sm leading-7 text-slate-700">{{ $review->body }}</p>
                            @if ($review->reply)
                                <div class="mt-3 rounded-xl border-r-4 border-violet-400 bg-violet-50 px-4 py-3 text-sm">
                                    <div class="mb-1 text-xs font-bold text-violet-700">پاسخ {{ $listing->title }}</div>
                                    <p class="leading-7 text-slate-700">{{ $review->reply }}</p>
                                </div>
                            @endif
                            @if (! $this->isOwner && $review->user_id !== auth()->id())
                                <button wire:click="openReport('{{ $review->id }}')" class="cursor-pointer mt-2 text-xs text-slate-400 hover:text-rose-600">🚩 گزارش این نظر</button>
                            @endif
                        </article>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-400">هنوز نظری ثبت نشده. اولین نفر باشید!</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- ===== ستون کناری: ساعات کاری، آدرس، صاحب پیج ===== --}}
        <aside class="space-y-4">
            @if ($hours)
                <section class="card p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold">🕘 ساعات کاری</h2>
                        <span @class(['chip', 'bg-emerald-50 text-emerald-700' => $hours['open'], 'bg-slate-100 text-slate-600' => ! $hours['open']])>{{ $hours['open'] ? 'الان باز است' : 'بسته است' }}</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ $hours['text'] }}</p>
                    <ul class="mt-3 space-y-1.5 text-sm">
                        @foreach ($listing->hours['days'] as $i => $day)
                            <li @class(['flex justify-between rounded-lg px-2 py-1', 'bg-violet-50 font-semibold' => $i === App\Models\Listing::todayIndex()])>
                                <span>{{ App\Models\Listing::WEEK_DAYS[$i] }}</span>
                                <span @class(['text-slate-400' => empty($day['open'])])>{{ App\Models\Listing::dayHoursLabel($day) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($listing->hasInstallments())
                <section class="card space-y-3 p-5 text-sm">
                    <h2 class="font-bold">💳 خرید قسطی</h2>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($listing->installmentMethods() as $method)
                            <span class="chip bg-violet-50 text-violet-700">{{ $method['icon'] }} {{ $method['label'] }}</span>
                        @endforeach
                    </div>
                    @if ($listing->installment_note)
                        <p class="leading-7 text-slate-600">{{ $listing->installment_note }}</p>
                    @endif
                </section>
            @endif

            <section class="card space-y-3 p-5 text-sm">
                <h2 class="font-bold">📍 تماس و آدرس</h2>
                @if ($listing->online_only)
                    <p class="text-slate-600">🌐 فقط فروش آنلاین (بدون مراجعه حضوری)</p>
                @endif
                @if ($listing->placeLabel())
                    <p class="text-slate-600">{{ $listing->placeLabel() }}</p>
                @endif
                @if ($listing->address)
                    <p class="leading-7 text-slate-600">{{ $listing->address }}</p>
                    <div class="flex gap-2">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" target="_blank" rel="noopener" class="cursor-pointer btn-light flex-1 py-2 text-xs">🗺️ نقشه</a>
                        <button x-on:click="navigator.clipboard.writeText(@js($listing->address)); $dispatch('toast', { message: 'آدرس کپی شد' })" class="cursor-pointer btn-light flex-1 py-2 text-xs">📋 کپی آدرس</button>
                    </div>
                @endif
                @if ($listing->phone)
                    <a href="tel:{{ $listing->phone }}" class="cursor-pointer btn-light w-full" dir="ltr">📞 {{ $listing->phone }}</a>
                @endif
                @if (! $listing->online_only && ! $listing->address && ! $listing->phone && ! $listing->placeLabel())
                    <p class="text-slate-400">اطلاعاتی ثبت نشده است.</p>
                @endif
            </section>

            <a href="{{ route('business.show', $listing->user) }}" wire:navigate class="cursor-pointer card flex items-center gap-3 p-5 transition hover:border-violet-200">
                <x-avatar :src="$listing->user->avatarUrl()" :name="$listing->user->name" :seed="$listing->user_id" class="size-12 text-lg" rounded="rounded-full" />
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1 font-bold">{{ $listing->user->name }} @if ($listing->user->isVerified()) <x-verified-badge /> @endif</div>
                    <div class="text-xs text-slate-500">مشاهده همه پیج‌ها و سایت‌های این کسب‌وکار ←</div>
                </div>
            </a>

            @unless ($this->isOwner)
                <x-website-promo variant="card" />
            @endunless
        </aside>
    </div>

    {{-- ===== پیج‌های مشابه ===== --}}
    @if ($similar->isNotEmpty())
        <section class="mt-10">
            <h2 class="mb-4 text-lg font-extrabold">پیج‌ها و سایت‌های مشابه</h2>
            <div class="grid gap-4 pt-2 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($similar as $item)
                    <x-listing-card :listing="$item" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===== نمایش بزرگ عکس ===== --}}
    @if ($photo !== null && $this->photos->has($photo))
        @php($current = $this->photos[$photo])
        <div class="fixed inset-0 z-[85] grid place-items-center bg-black/90 p-4" wire:click.self="$set('photo', null)"
             @keydown.escape.window="$wire.set('photo', null)" @keydown.arrow-left.window="$wire.nextPhoto(1)" @keydown.arrow-right.window="$wire.nextPhoto(-1)">
            <button wire:click="$set('photo', null)" class="cursor-pointer absolute top-4 left-4 rounded-full bg-white/10 px-3 py-1.5 text-white hover:bg-white/20">✕</button>
            <figure class="max-w-4xl text-center">
                <img src="{{ $current->url() }}" alt="{{ $current->caption ?: $listing->title }}" class="max-h-[80vh] rounded-xl object-contain">
                <figcaption class="mt-3 text-sm text-white/80">{{ $current->caption }} <span class="text-white/50">({{ fa_number($photo + 1) }} از {{ fa_number($this->photos->count()) }})</span></figcaption>
            </figure>
            @if ($this->photos->count() > 1)
                {{-- فلش‌ها SVG هستند چون کاراکترهای ‹ › در متن راست‌به‌چپ خودکار برعکس نمایش داده می‌شوند --}}
                <button wire:click="nextPhoto(-1)" class="cursor-pointer absolute top-1/2 right-4 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20" aria-label="عکس قبلی">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                </button>
                <button wire:click="nextPhoto(1)" class="cursor-pointer absolute top-1/2 left-4 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20" aria-label="عکس بعدی">
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg>
                </button>
            @endif
        </div>
    @endif

    {{-- ===== اشتراک‌گذاری ===== --}}
    @if ($showShare)
        <x-modal title="اشتراک‌گذاری پیج" close="$set('showShare', false)">
            <div class="grid grid-cols-3 gap-2 text-sm">
                <a href="https://t.me/share/url?url={{ urlencode($url) }}&text={{ urlencode($shareText) }}" target="_blank" rel="noopener" class="cursor-pointer btn-light flex-col py-4">✈️<span>تلگرام</span></a>
                <a href="https://wa.me/?text={{ urlencode($shareText.' '.$url) }}" target="_blank" rel="noopener" class="cursor-pointer btn-light flex-col py-4">💬<span>واتساپ</span></a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($shareText) }}" target="_blank" rel="noopener" class="cursor-pointer btn-light flex-col py-4">𝕏<span>ایکس</span></a>
                <a href="mailto:?subject={{ rawurlencode($shareText) }}&body={{ rawurlencode($url) }}" class="cursor-pointer btn-light flex-col py-4">✉️<span>ایمیل</span></a>
                <button x-on:click="navigator.clipboard.writeText(@js($url)); $dispatch('toast', { message: 'لینک کپی شد' })" class="cursor-pointer btn-light flex-col py-4">📋<span>کپی لینک</span></button>
                <button x-show="navigator.share" x-on:click="navigator.share({ title: @js($shareText), url: @js($url) })" class="cursor-pointer btn-light flex-col py-4">📤<span>سایر</span></button>
            </div>
            <input value="{{ $url }}" readonly class="input mt-4 text-left text-sm" dir="ltr" onclick="this.select()">
        </x-modal>
    @endif

    {{-- ===== گزارش تخلف ===== --}}
    @if ($reportTarget)
        <x-modal :title="$reportTarget === 'listing' ? 'گزارش تخلف این پیج' : 'گزارش این نظر'" close="$set('reportTarget', null)">
            <form wire:submit="submitReport" class="space-y-3">
                @foreach (App\Models\Report::REASONS[$reportTarget === 'listing' ? 'listing' : 'review'] as $reason)
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-sm has-checked:border-violet-500 has-checked:bg-violet-50">
                        <input type="radio" wire:model="reportReason" value="{{ $reason }}" class="accent-violet-600"> {{ $reason }}
                    </label>
                @endforeach
                @error('reportReason') <p class="error">{{ $message }}</p> @enderror
                <textarea wire:model="reportBody" rows="3" class="input" placeholder="توضیحات بیشتر (اختیاری)"></textarea>
                <button class="cursor-pointer btn-danger w-full">ارسال گزارش</button>
            </form>
        </x-modal>
    @endif
</div>
