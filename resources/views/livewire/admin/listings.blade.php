<div>
    <h1 class="mb-5 text-2xl font-extrabold">پیج‌ها</h1>

    <div class="mb-4 flex flex-wrap items-center gap-2">
        @foreach (['pending' => 'در انتظار تأیید', 'approved' => 'تأیید شده', 'rejected' => 'رد شده', 'all' => 'همه'] as $value => $label)
            <button wire:click="$set('status', '{{ $value }}')" @class(['filter-chip', 'filter-chip-active' => $status === $value])>
                {{ $label }} @if ($value !== 'all') ({{ fa_number($counts[$value] ?? 0) }}) @endif
            </button>
        @endforeach
        <input wire:model.live.debounce.400ms="q" class="input ms-auto w-full py-2 sm:w-64" placeholder="🔍 جستجو...">
    </div>

    @if ($listings->isEmpty())
        <x-empty icon="✅" title="موردی نیست" />
    @else
        <div class="space-y-3">
            @foreach ($listings as $listing)
                <article class="card p-4" wire:key="admin-listing-{{ $listing->id }}">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start">
                        <div class="flex min-w-0 flex-1 gap-3">
                            <div class="relative shrink-0">
                                <x-avatar :src="$listing->imageUrl()" :name="$listing->title" :seed="$listing->id" class="size-14 text-xl" />
                                <span class="absolute -bottom-1.5 -left-1.5 rounded-lg ring-2 ring-surface"><x-platform-icon :platform="$listing->platform" class="size-6 text-base" /></span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('listings.show', $listing) }}" wire:navigate class="font-bold hover:text-violet-700">{{ $listing->title }}</a>
                                    <x-status-badge :status="$listing->status" />
                                    @if ($listing->isFeatured()) <span class="chip bg-amber-100 text-amber-700">⭐ تا {{ fa_date($listing->featured_until, 'd MMMM') }}</span> @endif
                                </div>
                                <a href="{{ $listing->externalUrl() }}" target="_blank" rel="noopener" class="block text-right text-sm text-violet-700 hover:underline" dir="ltr">{{ $listing->displayHandle() }} ↗</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $listing->categoryLabel() }} · {{ $listing->user->name }} ({{ fa_digits($listing->user->mobile) }}) · {{ fa_date($listing->created_at) }}</p>
                                <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $listing->description }}</p>
                                @if ($listing->reject_reason)
                                    <p class="mt-1 text-xs text-rose-600">دلیل رد: {{ $listing->reject_reason }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 md:w-56 md:justify-end">
                            @if ($listing->status !== App\Enums\ModerationStatus::Approved)
                                <button wire:click="approve({{ $listing->id }})" class="btn-success py-2">تأیید</button>
                            @endif
                            @if ($listing->status !== App\Enums\ModerationStatus::Rejected)
                                <button wire:click="startReject({{ $listing->id }})" class="btn-danger py-2">رد</button>
                            @endif
                            @can('listings.feature')
                                @if ($listing->status === App\Enums\ModerationStatus::Approved)
                                    <button wire:click="feature({{ $listing->id }}, 7)" class="btn py-2 bg-amber-50 text-amber-700 hover:bg-amber-100">⭐ +۷ روز</button>
                                    @if ($listing->isFeatured())
                                        <button wire:click="feature({{ $listing->id }}, 0)" class="btn-light py-2">لغو ویژه</button>
                                    @endif
                                @endif
                            @endcan
                            @can('listings.delete')
                                <button wire:click="delete({{ $listing->id }})" wire:confirm="«{{ $listing->title }}» برای همیشه حذف شود؟" class="btn-light py-2 text-rose-600">حذف</button>
                            @endcan
                        </div>
                    </div>

                    @if ($rejecting === $listing->id)
                        <form wire:submit="reject" class="mt-3 flex flex-col gap-2 border-t border-slate-100 pt-3 sm:flex-row">
                            <input wire:model="reason" class="input flex-1" placeholder="دلیل رد (برای صاحب پیج ارسال می‌شود)" autofocus>
                            <button class="btn-danger">ثبت رد</button>
                            <button type="button" wire:click="$set('rejecting', null)" class="btn-light">انصراف</button>
                        </form>
                        @error('reason') <p class="error">{{ $message }}</p> @enderror
                    @endif
                </article>
            @endforeach
        </div>
        <div class="mt-4">{{ $listings->links() }}</div>
    @endif
</div>
