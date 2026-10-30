<x-dashboard-shell title="سئوی پیج">
    @if (! $this->listing)
        <x-empty icon="🔎" title="هنوز پیجی ثبت نکرده‌اید">
            <a href="{{ route('dashboard.listings.create') }}" wire:navigate class="btn-primary mt-4">ثبت اولین پیج</a>
        </x-empty>
    @else
        @php
            $grade = App\Services\ListingSeoAudit::grade($audit['score']);
            $approved = $this->listing->status === App\Enums\ModerationStatus::Approved;
        @endphp

        @if ($listings->count() > 1)
            <select wire:model.live="listingId" class="input mb-5 w-full sm:w-80">
                @foreach ($listings as $item)
                    <option value="{{ $item->id }}">{{ $item->title }}</option>
                @endforeach
            </select>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1fr_20rem]">
            <div class="space-y-6">
                {{-- ===== پیش‌نمایش و ویرایش نتیجه گوگل ===== --}}
                <section class="card p-6">
                    <h2 class="font-bold">پیش‌نمایش در نتایج گوگل</h2>
                    <p class="mt-1 text-sm text-slate-500">این متن زیر لینک صفحه شما در گوگل نمایش داده می‌شود. خالی بگذارید تا خودکار ساخته شود.</p>

                    {{-- نمای ساده نتیجه جستجو؛ رنگ‌ها ثابت مثل خود گوگل --}}
                    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4 text-right" dir="rtl">
                        <div class="truncate text-xs text-[#4d5156]" dir="ltr" style="text-align: right">{{ urldecode($url) }}</div>
                        <div class="mt-1 truncate text-lg leading-7 text-[#1a0dab]">{{ $previewTitle }}</div>
                        <div class="mt-1 line-clamp-2 text-sm leading-6 text-[#4d5156]">{{ $previewDescription }}</div>
                    </div>

                    <form wire:submit="save" class="mt-5 space-y-4">
                        <div>
                            @php($titleLength = mb_strlen($seoTitle))
                            <div class="flex justify-between">
                                <label class="label" for="seoTitle">عنوان در گوگل</label>
                                <span @class(['text-xs', 'text-rose-600' => $titleLength > 60, 'text-slate-400' => $titleLength <= 60])>{{ fa_number($titleLength) }} / ۶۰</span>
                            </div>
                            <input wire:model.live.debounce.300ms="seoTitle" id="seoTitle" class="input" placeholder="{{ $this->listing->autoSeoTitle() }}">
                            @error('seoTitle') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            @php($descLength = mb_strlen($seoDescription))
                            <div class="flex justify-between">
                                <label class="label" for="seoDescription">توضیح در گوگل</label>
                                <span @class(['text-xs', 'text-rose-600' => $descLength > 160, 'text-emerald-600' => $descLength >= 120 && $descLength <= 160, 'text-slate-400' => $descLength < 120])>{{ fa_number($descLength) }} / ۱۶۰</span>
                            </div>
                            <textarea wire:model.live.debounce.300ms="seoDescription" id="seoDescription" rows="3" class="input" placeholder="{{ $this->listing->autoSeoDescription() }}"></textarea>
                            <p class="mt-1 text-xs text-slate-400">بهترین طول ۱۲۰ تا ۱۶۰ کاراکتر است. یکی دو کلمه کلیدی (پیشنهادهای کنار صفحه) را طبیعی به کار ببرید.</p>
                            @error('seoDescription') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button class="btn-primary">ذخیره</button>
                            @if ($this->listing->seo->title || $this->listing->seo->description)
                                <button type="button" wire:click="useAuto" class="btn-light">برگشت به حالت خودکار</button>
                            @endif
                        </div>
                    </form>
                </section>

                {{-- ===== چک‌لیست ===== --}}
                <section class="card p-6">
                    <h2 class="font-bold">چک‌لیست بهبود</h2>
                    <ul class="mt-4 divide-y divide-slate-100">
                        @foreach ($audit['checks'] as $check)
                            <li class="flex gap-3 py-3">
                                <span @class(['mt-0.5 grid size-6 shrink-0 place-items-center rounded-full text-xs font-bold', 'bg-emerald-500 text-white' => $check['passed'], 'bg-slate-100 text-slate-400' => ! $check['passed']])>{{ $check['passed'] ? '✓' : '' }}</span>
                                <div class="flex-1">
                                    <div class="flex justify-between gap-2 text-sm">
                                        <span @class(['font-semibold', 'text-slate-400 line-through decoration-slate-300' => $check['passed']])>{{ $check['label'] }}</span>
                                        <span class="shrink-0 text-xs text-slate-400">{{ fa_number($check['weight']) }} امتیاز</span>
                                    </div>
                                    @unless ($check['passed'])
                                        <p class="mt-1 text-xs leading-6 text-slate-500">{{ $check['tip'] }}</p>
                                    @endunless
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('dashboard.listings.edit', $this->listing) }}" wire:navigate class="btn-light py-2 text-xs">✏️ ویرایش پیج</a>
                        <a href="{{ route('dashboard.reviews') }}" wire:navigate class="btn-light py-2 text-xs">💬 پاسخ به نظرها</a>
                    </div>
                </section>
            </div>

            <aside class="space-y-4">
                {{-- ===== امتیاز ===== --}}
                <section class="card p-6 text-center">
                    {{-- دایره پیشرفت با SVG --}}
                    <div class="relative mx-auto size-32">
                        <svg viewBox="0 0 36 36" class="size-32 -rotate-90">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="3" class="text-slate-100" />
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="{{ $grade['color'] }}" stroke-width="3" stroke-linecap="round" stroke-dasharray="{{ $audit['score'] }} 100" />
                        </svg>
                        <div class="absolute inset-0 grid place-items-center text-3xl font-black">{{ fa_number($audit['score']) }}</div>
                    </div>
                    <span class="chip mt-3 {{ $grade['class'] }}">امتیاز سئو: {{ $grade['label'] }}</span>
                </section>

                {{-- ===== کلمات کلیدی ===== --}}
                <section class="card p-5">
                    <h2 class="text-sm font-bold">🔑 کلمات کلیدی پیشنهادی</h2>
                    <p class="mt-1 text-xs leading-6 text-slate-500">مشتری‌ها احتمالاً با این عبارت‌ها جستجو می‌کنند. در توضیحات پیج و بیوی اینستاگرام از آن‌ها استفاده کنید.</p>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($audit['keywords'] as $keyword)
                            <button x-on:click="navigator.clipboard.writeText(@js($keyword)); $dispatch('toast', { message: 'کپی شد' })" class="filter-chip">{{ $keyword }}</button>
                        @endforeach
                    </div>
                </section>

                {{-- ===== ابزارهای بررسی ===== --}}
                <section class="card space-y-2 p-5 text-sm">
                    <h2 class="font-bold">🧰 ابزارهای بررسی</h2>
                    @if ($approved)
                        <a href="https://www.google.com/search?q={{ urlencode('site:'.$url) }}" target="_blank" rel="noopener" class="side-link">🔍 آیا گوگل صفحه را ایندکس کرده؟</a>
                        <a href="https://search.google.com/test/rich-results?url={{ urlencode($url) }}" target="_blank" rel="noopener" class="side-link">⭐ تست نمایش ستاره و اطلاعات در گوگل</a>
                        <a href="https://pagespeed.web.dev/analysis?url={{ urlencode($url) }}" target="_blank" rel="noopener" class="side-link">⚡ سرعت صفحه (PageSpeed)</a>
                    @else
                        <p class="text-xs text-slate-500">بعد از تأیید پیج، ابزارهای بررسی گوگل اینجا فعال می‌شوند.</p>
                    @endif
                    <button x-on:click="navigator.clipboard.writeText(@js($url)); $dispatch('toast', { message: 'لینک کپی شد' })" class="side-link w-full">📋 کپی لینک صفحه</button>
                    <a href="{{ route('dashboard.banner', $this->listing) }}" wire:navigate class="side-link">🖼️ بنر و لینک برای بیو و سایت</a>
                    <p class="pt-1 text-xs leading-6 text-slate-500">لینک صفحه نشونی را در بیوی اینستاگرام، کانال تلگرام و سایت خودتان بگذارید؛ هر لینک ورودی به دیده شدن صفحه در گوگل کمک می‌کند.</p>
                </section>
            </aside>
        </div>
    @endif
</x-dashboard-shell>
