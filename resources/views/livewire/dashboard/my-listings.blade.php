<x-dashboard-shell title="پیج‌های من">
    <x-slot:actions>
        <a href="{{ route('dashboard.listings.create') }}" wire:navigate class="btn-primary">+ ثبت پیج جدید</a>
    </x-slot:actions>

    @if ($listings->isEmpty())
        <x-empty icon="🚀" title="هنوز پیجی ثبت نکرده‌اید">
            <p>اولین پیج یا کانال خود را ثبت کنید تا بعد از تأیید در نشونی نمایش داده شود.</p>
            <a href="{{ route('dashboard.listings.create') }}" wire:navigate class="btn-primary mt-4">ثبت اولین پیج</a>
        </x-empty>
    @else
        <div class="stagger space-y-3">
            @foreach ($listings as $listing)
                <article class="card p-4" wire:key="listing-{{ $listing->id }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <div class="relative">
                                <x-avatar :src="$listing->imageUrl()" :name="$listing->title" :seed="$listing->id" class="size-14 text-xl" />
                                <span class="absolute -bottom-1.5 -left-1.5 rounded-lg ring-2 ring-surface"><x-platform-icon :platform="$listing->platform" class="size-6 text-base" /></span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('listings.show', $listing) }}" wire:navigate class="truncate font-bold hover:text-violet-700">{{ $listing->title }}</a>
                                    <x-status-badge :status="$listing->status" />
                                    @if ($listing->isFeatured())
                                        <span class="chip bg-amber-100 text-amber-700" title="تا {{ fa_date($listing->featured_until) }}">⭐ ویژه تا {{ fa_date($listing->featured_until, 'd MMMM') }}</span>
                                    @endif
                                </div>
                                <p class="truncate text-right text-sm text-slate-500" dir="ltr">{{ $listing->displayHandle() }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $listing->categoryLabel() }} · 👁️ {{ fa_number($listing->views) }} · 🖱️ {{ fa_number($listing->clicks) }} · ❤️ {{ fa_number($listing->fans_count) }} · 💬 {{ fa_number($listing->reviews_count) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('dashboard.listings.edit', $listing) }}" wire:navigate class="btn-light py-2">✏️ ویرایش</a>
                            @php($grade = App\Services\ListingSeoAudit::grade($seoScores[$listing->id]))
                            <a href="{{ route('dashboard.seo', $listing) }}" wire:navigate class="btn-light py-2" title="امتیاز سئو و راه‌های بهبود">
                                🔎 سئو <span class="chip {{ $grade['class'] }} px-2 py-0.5">{{ fa_number($seoScores[$listing->id]) }}</span>
                            </a>
                            @if ($listing->status === App\Enums\ModerationStatus::Approved)
                                <a href="{{ route('dashboard.feature', $listing) }}" wire:navigate class="btn py-2 bg-amber-50 text-amber-700 hover:bg-amber-100">⭐ {{ $listing->isFeatured() ? 'تمدید ویژه' : 'ویژه کردن' }}</a>
                                <a href="{{ route('dashboard.banner', $listing) }}" wire:navigate class="btn-light py-2">🖼️ بنر</a>
                            @endif
                            <button wire:click="delete({{ $listing->id }})" wire:confirm="«{{ $listing->title }}» برای همیشه حذف شود؟" class="btn-danger py-2">حذف</button>
                        </div>
                    </div>

                    @if ($listing->status === App\Enums\ModerationStatus::Rejected && $listing->reject_reason)
                        <p class="mt-3 rounded-xl bg-rose-50 px-3 py-2 text-sm text-rose-700">دلیل رد: {{ $listing->reject_reason }} — پیج را اصلاح کنید تا دوباره بررسی شود.</p>
                    @elseif ($listing->status === App\Enums\ModerationStatus::Pending)
                        <p class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-sm text-amber-700">⏳ پیج در صف بررسی مدیر است و بعد از تأیید نمایش داده می‌شود.</p>
                    @endif
                    @if ($listing->pending_photos_count)
                        <p class="mt-2 text-xs text-slate-500">🖼️ {{ fa_number($listing->pending_photos_count) }} تصویر در انتظار تأیید</p>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</x-dashboard-shell>
