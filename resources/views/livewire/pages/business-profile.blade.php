<div class="mx-auto max-w-6xl px-4 py-8">
    <section class="card overflow-hidden">
        <div class="h-28 bg-linear-to-l from-violet-600 to-fuchsia-500"></div>
        <div class="flex flex-col gap-4 px-6 pb-6 sm:flex-row sm:items-end">
            <x-avatar :src="$user->avatarUrl()" :name="$user->name" :seed="$user->id" class="-mt-12 size-24 text-4xl ring-4 ring-surface" rounded="rounded-full" />
            <div class="flex-1">
                <h1 class="flex items-center gap-2 text-2xl font-extrabold">{{ $user->name }} @if ($user->isVerified()) <x-verified-badge class="size-5" /> @endif</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ collect([$user->placeLabel() ? '📍 '.$user->placeLabel() : null, 'عضو از '.fa_date($user->created_at, 'MMMM y')])->filter()->implode(' · ') }}
                </p>
            </div>
            <div class="flex gap-6 text-center text-sm">
                <div><div class="text-xl font-black">{{ fa_number($listings->count()) }}</div><div class="text-slate-500">پیج</div></div>
                <div><div class="text-xl font-black">{{ fa_compact($followers) }}</div><div class="text-slate-500">دنبال‌کننده</div></div>
                @if ($rating)
                    <div><div class="text-xl font-black text-amber-500">★ {{ fa_number($rating, 1) }}</div><div class="text-slate-500">امتیاز</div></div>
                @endif
            </div>
        </div>
        @if ($user->bio)
            <p class="border-t border-slate-100 px-6 py-4 leading-8 text-slate-600">{{ $user->bio }}</p>
        @endif
    </section>

    <h2 class="mt-8 mb-4 text-lg font-extrabold">پیج‌ها و کانال‌ها</h2>
    @if ($listings->isEmpty())
        <x-empty title="هنوز پیج تأییدشده‌ای ندارد" />
    @else
        <div class="stagger grid gap-4 pt-2 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($listings as $listing)
                <x-listing-card :listing="$listing" />
            @endforeach
        </div>
    @endif
</div>
