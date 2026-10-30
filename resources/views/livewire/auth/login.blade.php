<x-auth-card title="ورود به نشونی" subtitle="برای مدیریت پیج‌ها وارد حساب خود شوید">
    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="label" for="mobile">شماره موبایل</label>
            <input wire:model="mobile" id="mobile" class="input text-left" dir="ltr" inputmode="numeric" placeholder="09121234567" autofocus>
            @error('mobile') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <div class="flex items-center justify-between">
                <label class="label" for="password">رمز عبور</label>
                <a href="{{ route('password.request') }}" wire:navigate class="mb-1.5 text-xs text-violet-700 hover:underline">فراموشی رمز</a>
            </div>
            <input wire:model="password" id="password" type="password" class="input text-left" dir="ltr">
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label" for="captcha">کد امنیتی</label>
            <div class="flex items-center gap-2">
                <input wire:model="captcha" id="captcha" class="input min-w-0 flex-1 text-center tracking-[0.4em]" dir="ltr" inputmode="numeric" maxlength="5" autocomplete="off" placeholder="۵ رقم">
                {{-- شناسه کامپوننت + شماره در آدرس تا مرورگر تصویر قدیمی را از کش نیاورد --}}
                <img src="{{ url('captcha/neshooni') }}?{{ $this->getId() }}-{{ $captchaKey }}" alt="کد امنیتی" class="h-[46px] w-[166px] shrink-0 rounded-xl border border-slate-200 object-cover" wire:key="captcha-{{ $captchaKey }}">
                <button type="button" wire:click="refreshCaptcha" class="shrink-0 rounded-xl p-2.5 text-slate-500 hover:bg-slate-100" title="تصویر جدید" aria-label="تصویر جدید">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
                </button>
            </div>
            @error('captcha') <p class="error">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input wire:model="remember" type="checkbox" class="rounded accent-violet-600"> مرا به خاطر بسپار
        </label>
        <button class="btn-primary w-full" wire:loading.attr="disabled">
            <span wire:loading.remove>ورود</span>
            <span wire:loading>در حال ورود...</span>
        </button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">
        حساب ندارید؟ <a href="{{ route('register') }}" wire:navigate class="font-semibold text-violet-700 hover:underline">ثبت‌نام رایگان</a>
    </p>
</x-auth-card>
