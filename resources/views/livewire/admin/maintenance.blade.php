<div>
    <h1 class="mb-5 text-2xl font-extrabold">حالت تعمیر</h1>

    <div @class(['card mb-5 flex items-center gap-4 p-5', 'ring-2 ring-amber-400' => $active])>
        <span @class(['grid size-12 shrink-0 place-items-center rounded-2xl text-2xl', 'bg-amber-100' => $active, 'bg-emerald-50' => ! $active])>{{ $active ? '🛠️' : '✅' }}</span>
        <div class="min-w-0 flex-1">
            @if ($active)
                <div class="font-bold text-amber-700">سایت در حالت تعمیر است</div>
                <p class="mt-1 text-sm leading-7 text-slate-500">
                    از {{ fa_datetime($startedAt) }}
                    @if ($endsAt)
                        · بازگشت تقریبی: {{ fa_datetime($endsAt) }}
                        @if ($endsAt->isPast()) <span class="text-rose-600">(گذشته)</span> @endif
                    @endif
                </p>
            @else
                <div class="font-bold text-emerald-700">سایت برای همه باز است</div>
                <p class="mt-1 text-sm text-slate-500">با روشن کردن حالت تعمیر، بازدیدکننده‌ها و کاربران عادی صفحه «در حال به‌روزرسانی» را می‌بینند.</p>
            @endif
        </div>
        @if ($active)
            <button wire:click="disable" wire:confirm="سایت دوباره برای همه باز شود؟" class="cursor-pointer btn-primary shrink-0">خاموش کردن</button>
        @endif
    </div>

    <form wire:submit="enable" class="card space-y-4 p-5">
        <div>
            <label class="label">پیام برای بازدیدکننده‌ها</label>
            <textarea wire:model="message" rows="3" class="input" placeholder="سایت برای به‌روزرسانی چند دقیقه در دسترس نیست. به‌زودی برمی‌گردیم."></textarea>
            <p class="mt-1 text-xs text-slate-400">اگر خالی بماند، همین متن پیش‌فرض نمایش داده می‌شود.</p>
            @error('message') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label">مدت تقریبی</label>
            <select wire:model="duration" class="input">
                <option value="">نامشخص</option>
                @foreach ($this->durations() as $minutes => $label)
                    <option value="{{ $minutes }}">{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-400">زمان بازگشت به بازدیدکننده‌ها نشان داده می‌شود؛ سایت خودکار باز نمی‌شود.</p>
            @error('duration') <p class="error">{{ $message }}</p> @enderror
        </div>
        <button @if (! $active) wire:confirm="سایت برای بازدیدکننده‌ها بسته شود؟" @endif class="cursor-pointer btn-primary w-full">
            {{ $active ? 'به‌روزرسانی پیام' : 'روشن کردن حالت تعمیر' }}
        </button>
    </form>

    <div class="mt-5 rounded-2xl bg-slate-50 p-4 text-sm leading-7 text-slate-500">
        در حالت تعمیر، کارکنان پنل سایت را عادی می‌بینند و صفحه ورود باز می‌ماند تا بتوانید وارد شوید و آن را خاموش کنید.
    </div>
</div>
