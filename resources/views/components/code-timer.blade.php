@props(['expiresAt', 'resend'])

{{--
    شمارش معکوس دو دقیقه و نیمه کد پیامکی. تا پایان زمان، دکمه ارسال دوباره غیرفعال است.
    زمان باقی‌مانده در سرور حساب می‌شود تا ساعت نادرست دستگاه کاربر روی تایمر اثر نگذارد؛
    wire:key با هر کد تازه عوض می‌شود تا تایمر از نو شروع شود.
--}}
<div wire:key="code-timer-{{ $expiresAt }}"
     x-data="{
        left: {{ max(0, $expiresAt - now()->timestamp) }},
        timer: null,
        init() {
            const end = Date.now() + this.left * 1000;
            this.timer = setInterval(() => {
                this.left = Math.max(0, Math.ceil((end - Date.now()) / 1000));
                if (this.left === 0) clearInterval(this.timer);
            }, 250);
        },
        destroy() { clearInterval(this.timer) },
        get label() {
            const m = Math.floor(this.left / 60), s = String(this.left % 60).padStart(2, '0');
            return `${m}:${s}`.replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
        },
     }"
     {{ $attributes->merge(['class' => 'flex items-center justify-between gap-3 text-sm']) }}>
    <p x-show="left > 0" class="text-slate-500">
        اعتبار کد: <span class="inline-block min-w-[2.5rem] font-semibold tabular-nums text-violet-700" dir="ltr" x-text="label"></span>
    </p>
    <p x-show="left === 0" x-cloak class="text-rose-600">کد منقضی شد؛ کد تازه بگیرید.</p>
    <button type="button" wire:click="{{ $resend }}" wire:loading.attr="disabled" wire:target="{{ $resend }}"
            x-bind:disabled="left > 0"
            class="cursor-pointer text-violet-700 hover:underline disabled:cursor-not-allowed disabled:text-slate-400 disabled:no-underline">
        ارسال دوباره کد
    </button>
</div>
