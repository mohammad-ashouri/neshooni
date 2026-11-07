<div>
    <h1 class="mb-5 text-2xl font-extrabold">نماد اینماد</h1>

    <form wire:submit="save" class="card space-y-4 p-5">
        <div>
            <label class="label">کد اینماد</label>
            <textarea wire:model="code" rows="6" dir="ltr" class="input font-mono text-xs" placeholder="<a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=...'>...</a>"></textarea>
            <p class="mt-1 text-xs leading-6 text-slate-400">کد را کامل از پنل enamad.ir کپی کنید. اگر خالی بماند، باکس اینماد در فوتر نمایش داده نمی‌شود.</p>
            @error('code') <p class="error">{{ $message }}</p> @enderror
        </div>
        <button class="cursor-pointer btn-primary w-full">ذخیره</button>
    </form>

    <div class="card mt-5 p-5">
        <div class="mb-3 font-bold">پیش‌نمایش فوتر</div>
        @if ($saved)
            <div class="inline-grid size-28 place-items-center rounded-2xl border border-slate-200 bg-white p-2">{!! $saved !!}</div>
            <p class="mt-2 text-xs text-slate-400">اگر تصویر نمایش داده نمی‌شود، دامنه سایت هنوز در اینماد تأیید نشده یا کد اشتباه است.</p>
        @else
            <p class="text-sm text-slate-500">هنوز کدی ثبت نشده است.</p>
        @endif
    </div>
</div>
