<x-dashboard-shell title="اعلان و خبرنامه">
    <div class="grid gap-6 xl:grid-cols-2">
        {{-- ===== خبرنامه ===== --}}
        <form wire:submit="save" class="card h-fit space-y-4 p-6">
            <div>
                <h2 class="font-bold">📬 خبرنامه دسته‌ها</h2>
                <p class="mt-1 text-sm leading-7 text-slate-500">وقتی پیج تازه‌ای در دسته‌های انتخابی شما منتشر شود، اعلان می‌گیرید. انتخاب هر دسته شامل همه زیردسته‌هایش هم می‌شود.</p>
            </div>

            <label class="flex items-center gap-2 rounded-xl bg-violet-50 px-3 py-2.5 text-sm font-semibold">
                <input type="checkbox" wire:model="general" class="accent-violet-600"> همه پیج‌های جدید (خبرنامه عمومی)
            </label>

            <div class="max-h-[28rem] space-y-1 overflow-y-auto pe-1">
                @foreach ($roots as $root)
                    {{-- details/summary برای باز و بسته شدن، بدون نیاز به جاوااسکریپت --}}
                    <details class="group rounded-xl border border-slate-200" wire:key="root-{{ $root->id }}" @if (collect($selected)->intersect($root->selfAndDescendantIds())->isNotEmpty()) open @endif>
                        <summary class="flex cursor-pointer list-none items-center gap-2 px-3 py-2.5 text-sm">
                            <input type="checkbox" wire:model="selected" value="{{ $root->id }}" class="accent-violet-600" onclick="event.stopPropagation()">
                            <span>{{ $root->icon }} {{ $root->name }}</span>
                            @if ($root->subs()->isNotEmpty())
                                <span class="ms-auto text-xs text-slate-400 transition group-open:rotate-90">‹</span>
                            @endif
                        </summary>
                        @if ($root->subs()->isNotEmpty())
                            <div class="space-y-1 border-t border-slate-100 px-3 py-2">
                                @foreach ($root->subs() as $sub)
                                    <label class="flex items-center gap-2 py-1 ps-5 text-sm">
                                        <input type="checkbox" wire:model="selected" value="{{ $sub->id }}" class="accent-violet-600"> {{ $sub->name }}
                                    </label>
                                    @foreach ($sub->subs() as $leaf)
                                        <label class="flex items-center gap-2 py-1 ps-11 text-sm text-slate-600">
                                            <input type="checkbox" wire:model="selected" value="{{ $leaf->id }}" class="accent-violet-600"> {{ $leaf->name }}
                                        </label>
                                    @endforeach
                                @endforeach
                            </div>
                        @endif
                    </details>
                @endforeach
            </div>

            <button class="btn-primary">ذخیره خبرنامه</button>

            @if ($interests)
                <div class="border-t border-slate-100 pt-4">
                    <h3 class="text-sm font-bold">✨ علاقه‌مندی‌های شما</h3>
                    <p class="mt-1 text-xs text-slate-500">بر اساس پیج‌هایی که دیده‌اید، دنبال کرده‌اید یا درباره‌شان نظر داده‌اید.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($interests as ['category' => $category, 'score' => $score])
                            <a href="{{ $category->url() }}" wire:navigate class="filter-chip">{{ $category->root()->icon }} {{ $category->name }} <span class="text-slate-400">{{ fa_number($score) }}</span></a>
                        @endforeach
                    </div>
                </div>
            @endif
        </form>

        {{-- ===== اعلان‌ها ===== --}}
        <section class="card h-fit p-6">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-bold">🔔 اعلان‌ها</h2>
                @if ($notifications->whereNull('read_at')->isNotEmpty())
                    <button wire:click="markAllRead" class="text-xs text-violet-700 hover:underline">همه خوانده شد</button>
                @endif
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($notifications as $notification)
                    <a href="{{ $notification->data['url'] }}" wire:navigate class="flex gap-2 py-3 text-sm">
                        <span @class(['mt-2 size-2 shrink-0 rounded-full', 'bg-violet-500' => ! $notification->read_at, 'bg-slate-200' => $notification->read_at])></span>
                        <span>
                            <span class="block leading-7">{{ $notification->data['text'] }}</span>
                            <span class="text-xs text-slate-400">{{ fa_datetime($notification->created_at) }}</span>
                        </span>
                    </a>
                @empty
                    <p class="py-8 text-center text-sm text-slate-400">اعلانی ندارید.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-dashboard-shell>
