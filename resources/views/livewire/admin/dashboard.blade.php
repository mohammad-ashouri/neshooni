<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-extrabold">داشبورد</h1>
        <div class="flex rounded-xl border border-slate-200 bg-surface p-1">
            @foreach ([7 => '۷ روز', 30 => '۳۰ روز', 90 => '۹۰ روز'] as $value => $label)
                <button wire:click="$set('days', {{ $value }})" @class(['rounded-lg px-3 py-1.5 text-sm', 'bg-violet-600 text-white' => $days === $value, 'text-slate-600' => $days !== $value])>{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" wire:loading.class="opacity-60">
        @foreach ($tiles as [$label, $value, $change])
            <div class="card p-5">
                <div class="text-sm text-slate-500">{{ $label }}</div>
                <div class="mt-2 text-3xl font-black">{{ fa_number($value) }}</div>
                @if ($change !== null)
                    <div @class(['mt-1 text-xs', 'text-emerald-600' => $change >= 0, 'text-rose-600' => $change < 0])>{{ $change >= 0 ? '▲' : '▼' }} {{ fa_number(abs($change)) }}٪ نسبت به دوره قبل</div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_18rem]">
        <div class="card p-6">
            <h2 class="mb-4 font-bold">بازدید و کلیک کل سایت</h2>
            <x-chart.line :series="$series" />
        </div>

        <div class="space-y-4">
            @if ($revenue !== null)
                <div class="card p-5">
                    <div class="text-sm text-slate-500">💰 درآمد {{ fa_number($days) }} روز اخیر</div>
                    <div class="mt-2 text-2xl font-black">{{ fa_price($revenue) }}</div>
                </div>
            @endif
            <div class="card divide-y divide-slate-100">
                @foreach ($pending as [$label, $count, $route, $permission])
                    @can($permission)
                        <a href="{{ route($route) }}" wire:navigate class="flex items-center justify-between px-5 py-3 text-sm hover:bg-slate-50">
                            <span>{{ $label }}</span>
                            <span @class(['chip', 'bg-rose-50 text-rose-700' => $count, 'bg-slate-100 text-slate-500' => ! $count])>{{ fa_number($count) }}</span>
                        </a>
                    @endcan
                @endforeach
            </div>
            <div class="card grid grid-cols-3 p-4 text-center text-sm">
                <div><div class="text-lg font-black">{{ fa_number($totals['listings']) }}</div><div class="text-xs text-slate-500">پیج فعال</div></div>
                <div><div class="text-lg font-black">{{ fa_number($totals['users']) }}</div><div class="text-xs text-slate-500">کاربر</div></div>
                <div><div class="text-lg font-black">{{ fa_number($totals['reviews']) }}</div><div class="text-xs text-slate-500">نظر</div></div>
            </div>
        </div>
    </div>

    <div class="card p-6">
        <h2 class="mb-4 font-bold">🔥 پربازدیدترین پیج‌ها</h2>
        <x-chart.bars :items="$top->map(fn ($l) => ['label' => $l->title.' ('.$l->platform->label.')', 'value' => $l->views])->all()" />
    </div>
</div>
