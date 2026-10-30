<div class="space-y-6">
    <h1 class="text-2xl font-extrabold">خبرنامه</h1>

    <div class="grid gap-4 sm:grid-cols-4">
        @foreach ([['اشتراک‌ها', $total], ['کاربر عضو', $members], ['مهمان', $guests], ['خبرنامه عمومی', $general]] as [$label, $value])
            <div class="card p-5">
                <div class="text-sm text-slate-500">{{ $label }}</div>
                <div class="mt-2 text-2xl font-black">{{ fa_number($value) }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="card p-6">
            <h2 class="mb-4 font-bold">مشترکان هر دسته</h2>
            @if ($categories->isEmpty())
                <p class="text-sm text-slate-400">هنوز مشترکی برای دسته‌ها نیست.</p>
            @else
                <x-chart.bars :items="$categories->map(fn ($row) => ['label' => $row['category']->pathLabel(), 'value' => $row['count']])->all()" />
            @endif
        </div>

        <div class="card p-6">
            <h2 class="mb-1 font-bold">صف ارسال برای مهمان‌ها</h2>
            <p class="mb-4 text-xs text-slate-500">پیام‌هایی که باید با پیامک یا ایمیل برای مشترکان مهمان فرستاده شوند.</p>
            <div class="divide-y divide-slate-100 text-sm">
                @forelse ($outbox as $item)
                    <div class="py-2.5">
                        <div class="flex justify-between gap-2">
                            <span class="font-mono text-xs" dir="ltr">{{ $item->contact }}</span>
                            <span @class(['chip', 'bg-emerald-50 text-emerald-700' => $item->sent_at, 'bg-amber-50 text-amber-700' => ! $item->sent_at])>{{ $item->sent_at ? 'ارسال شد' : 'در صف' }}</span>
                        </div>
                        <p class="mt-1 text-slate-600">{{ $item->body }}</p>
                    </div>
                @empty
                    <p class="py-6 text-center text-slate-400">صف خالی است.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
