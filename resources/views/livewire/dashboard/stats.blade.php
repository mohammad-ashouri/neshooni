<x-dashboard-shell title="آمار بازدید">
    <x-slot:actions>
        <div class="flex flex-wrap gap-2">
            <select wire:model.live="listingId" class="input w-auto py-2">
                <option value="0">همه پیج‌ها</option>
                @foreach ($listings as $item)
                    <option value="{{ $item->id }}">{{ $item->title }}</option>
                @endforeach
            </select>
            <div class="flex rounded-xl border border-slate-200 bg-surface p-1">
                @foreach ([7 => '۷ روز', 30 => '۳۰ روز', 90 => '۹۰ روز'] as $value => $label)
                    <button wire:click="$set('days', {{ $value }})" @class(['rounded-lg px-3 py-1.5 text-sm', 'bg-violet-600 text-white' => $days === $value, 'text-slate-600' => $days !== $value])>{{ $label }}</button>
                @endforeach
            </div>
        </div>
    </x-slot:actions>

    @if ($listings->isEmpty())
        <x-empty icon="📊" title="هنوز پیجی ندارید که آمارش را ببینید" />
    @else
        <div wire:loading.class="opacity-60" class="space-y-6">
            {{-- کاشی‌های آمار --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([['👁️ بازدید', $views], ['🖱️ کلیک روی لینک', $clicks]] as [$label, $item])
                    <div class="card p-5">
                        <div class="text-sm text-slate-500">{{ $label }}</div>
                        <div class="mt-2 text-3xl font-black">{{ fa_number($item['value']) }}</div>
                        @if ($item['change'] !== null)
                            <div @class(['mt-1 text-xs', 'text-emerald-600' => $item['change'] >= 0, 'text-rose-600' => $item['change'] < 0])>
                                {{ $item['change'] >= 0 ? '▲' : '▼' }} {{ fa_number(abs($item['change'])) }}٪ نسبت به {{ fa_number($days) }} روز قبل
                            </div>
                        @endif
                    </div>
                @endforeach
                <div class="card p-5">
                    <div class="text-sm text-slate-500">❤️ دنبال‌کننده در نشونی</div>
                    <div class="mt-2 text-3xl font-black">{{ fa_number($followers) }}</div>
                </div>
                <div class="card p-5">
                    <div class="text-sm text-slate-500">⭐ میانگین امتیاز</div>
                    <div class="mt-2 text-3xl font-black">{{ $rating['count'] ? fa_number($rating['average'], 1) : '—' }}</div>
                    <div class="mt-1 text-xs text-slate-500">از {{ fa_number($rating['count']) }} نظر تأییدشده</div>
                </div>
            </div>

            <div class="card p-6">
                <h2 class="mb-4 font-bold">روند بازدید و کلیک</h2>
                <x-chart.line :series="$series" />
            </div>

            @if (! $listingId && count($bars) > 1)
                <div class="card p-6">
                    <h2 class="mb-4 font-bold">بازدید هر پیج در {{ fa_number($days) }} روز اخیر</h2>
                    <x-chart.bars :items="$bars" />
                </div>
            @endif
        </div>
    @endif
</x-dashboard-shell>
