<x-auth-card title="ثبت‌نام رایگان" :subtitle="$step === 1 ? ($intent === 'website' ? 'در کمتر از یک دقیقه حساب بسازید و سایت فروشگاهی خود را معرفی کنید' : 'در کمتر از یک دقیقه حساب بسازید و پیج یا سایت فروشگاهی خود را معرفی کنید') : 'کد پیامک‌شده به '.fa_digits($mobile).' را وارد کنید'">
    @if ($step === 1)
        <form wire:submit="sendCode" class="space-y-4">
            <div>
                <label class="label" for="name">نام کسب‌وکار یا نام شما</label>
                <input wire:model="name" id="name" class="input" placeholder="مثلاً گالری ماهور" autofocus>
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="mobile">شماره موبایل</label>
                <input wire:model="mobile" id="mobile" class="input text-left" dir="ltr" inputmode="numeric" placeholder="09121234567">
                @error('mobile') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="password">رمز عبور</label>
                    <input wire:model="password" id="password" type="password" class="input text-left" dir="ltr">
                    @error('password') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="password_confirmation">تکرار رمز</label>
                    <input wire:model="password_confirmation" id="password_confirmation" type="password" class="input text-left" dir="ltr">
                </div>
            </div>
            <button class="cursor-pointer btn-primary w-full" wire:loading.attr="disabled">دریافت کد تأیید</button>
        </form>
    @else
        <form wire:submit="register" class="space-y-4">
            <div>
                <label class="label" for="code">کد ۵ رقمی</label>
                <input wire:model="code" id="code" class="input text-center tracking-[0.5em]" dir="ltr" inputmode="numeric" maxlength="5" autocomplete="one-time-code" autofocus>
                @error('code') <p class="error">{{ $message }}</p> @enderror
            </div>
            <x-code-timer :expires-at="$codeExpiresAt" resend="sendCode" />
            <button class="cursor-pointer btn-primary w-full" wire:loading.attr="disabled">تأیید و ساخت حساب</button>
            <button type="button" wire:click="$set('step', 1)" class="cursor-pointer w-full text-sm text-slate-500 hover:text-slate-700">ویرایش اطلاعات</button>
        </form>
    @endif
    <p class="mt-6 text-center text-sm text-slate-500">
        قبلاً ثبت‌نام کرده‌اید؟ <a href="{{ route('login') }}" wire:navigate class="cursor-pointer font-semibold text-violet-700 hover:underline">وارد شوید</a>
    </p>
</x-auth-card>
