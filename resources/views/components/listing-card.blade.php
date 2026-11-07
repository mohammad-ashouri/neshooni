{{--
    کارت پیج در لیست‌ها. برای امتیاز و تعداد تصاویر، کوئری را با ->forCards() بگیرید.
    ad: کارت در جایگاه تبلیغاتی نمایش داده می‌شود و برچسب «آگهی ویژه» می‌گیرد.
    در نتایج عادی کارت پیج ویژه با بقیه یکسان است تا رتبه‌بندی مستقل از پرداخت دیده شود.
--}}
@props(['listing', 'ad' => false])

@php
    $featured = $ad && $listing->isFeatured();
    $hours = $listing->hoursStatus();
@endphp

<a href="{{ route('listings.show', $listing) }}" wire:navigate
   {{ $attributes->class([
       'group relative flex h-full cursor-pointer flex-col rounded-2xl border p-4 transition duration-300 hover:-translate-y-1 hover:shadow-xl',
       'border-amber-300 bg-linear-to-b from-amber-50 to-surface ring-1 ring-amber-200 hover:shadow-amber-500/15' => $featured,
       'border-slate-200 bg-surface hover:border-violet-200 hover:shadow-violet-500/10' => ! $featured,
   ]) }}>
    @if ($featured)
        <span class="absolute -top-2.5 left-4 rounded-full bg-linear-to-l from-amber-400 to-orange-500 px-2.5 py-0.5 text-[11px] font-bold text-white shadow-sm shadow-orange-500/30" title="جایگاه تبلیغاتی؛ صاحب پیج برای نمایش آن هزینه پرداخت کرده">آگهی ویژه</span>
    @endif

    <div class="flex items-start gap-3">
        <div class="relative transition duration-300 group-hover:scale-105 group-hover:-rotate-3">
            <x-avatar :src="$listing->imageUrl()" :name="$listing->title" :seed="$listing->id" class="size-14 text-xl" />
            <span class="absolute -bottom-1.5 -left-1.5 rounded-lg ring-2 ring-surface"><x-platform-icon :platform="$listing->platform" class="size-6 text-base" /></span>
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="flex items-center gap-1 font-bold text-slate-800 group-hover:text-violet-700">
                <span class="truncate">{{ $listing->title }}</span>
                @if ($listing->user->isVerified()) <x-verified-badge /> @endif
            </h3>
            <p class="truncate text-right text-sm text-slate-500" dir="ltr">{{ $listing->displayHandle() }}</p>
            @if ($listing->city || $hours)
                <p class="mt-0.5 flex items-center gap-2 text-xs text-slate-400">
                    @if ($listing->city) <span>📍 {{ $listing->city }}</span> @endif
                    @if ($hours)
                        <span @class(['inline-flex items-center gap-1', 'text-emerald-600' => $hours['open']])>
                            <span @class(['size-1.5 rounded-full', 'bg-emerald-500' => $hours['open'], 'bg-slate-300' => ! $hours['open']])></span>
                            {{ $hours['open'] ? 'الان باز' : 'بسته' }}
                        </span>
                    @endif
                </p>
            @endif
        </div>
    </div>

    <p class="mt-3 line-clamp-2 min-h-10 text-sm leading-6 text-slate-600">{{ $listing->description }}</p>

    <div class="mt-auto flex items-center justify-between gap-2 pt-4 text-xs">
        <span class="chip truncate bg-slate-100 text-slate-600">{{ $listing->category?->root()->icon }} {{ $listing->category?->name ?? 'بدون دسته' }}</span>
        <span class="whitespace-nowrap text-slate-500">
            {{ collect([
                $listing->rating ? '★ '.fa_number($listing->rating, 1) : null,
                $listing->followers ? fa_compact($listing->followers).' دنبال‌کننده' : null,
                $listing->photos_count ? '🖼️ '.fa_number($listing->photos_count) : null,
            ])->filter()->implode(' · ') }}
        </span>
    </div>
</a>
