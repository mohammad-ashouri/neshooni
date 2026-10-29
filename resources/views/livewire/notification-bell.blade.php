<div class="relative" x-data="{ open: false }" @click.outside="open = false" wire:poll.60s>
    <button @click="open = !open" class="relative rounded-lg p-2 text-slate-600 hover:bg-slate-100" aria-label="اعلان‌ها">
        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0"/></svg>
        @if ($unread)
            <span class="absolute top-1 left-1 grid min-w-4 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">{{ fa_number($unread) }}</span>
        @endif
    </button>

    <div x-show="open" x-transition x-cloak class="absolute -left-10 mt-2 w-80 rounded-xl border border-slate-200 bg-surface text-sm shadow-xl sm:left-0">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <span class="font-bold">اعلان‌ها</span>
            @if ($unread)
                <button wire:click="markAllRead" class="text-xs text-violet-700 hover:underline">همه خوانده شد</button>
            @endif
        </div>
        <div class="max-h-96 overflow-y-auto p-1.5">
            @forelse ($notifications as $notification)
                <button wire:click="open('{{ $notification->id }}')" @class(['flex w-full gap-2 rounded-lg px-3 py-2.5 text-right hover:bg-slate-50', 'bg-violet-50/60' => ! $notification->read_at])>
                    <span @class(['mt-1.5 size-2 shrink-0 rounded-full', 'bg-violet-500' => ! $notification->read_at, 'bg-transparent' => $notification->read_at])></span>
                    <span>
                        <span class="block leading-6 text-slate-700">{{ $notification->data['text'] }}</span>
                        <span class="text-xs text-slate-400">{{ fa_datetime($notification->created_at) }}</span>
                    </span>
                </button>
            @empty
                <p class="px-3 py-8 text-center text-slate-400">اعلانی ندارید</p>
            @endforelse
        </div>
    </div>
</div>
