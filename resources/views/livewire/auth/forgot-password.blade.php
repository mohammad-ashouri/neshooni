<x-auth-card title="بازیابی رمز عبور" :subtitle="$step === 1 ? 'شماره موبایل حساب خود را وارد کنید' : 'کد پیامک‌شده به '.fa_digits($mobile).' را وارد کنید'">
    @if ($step === 1)
        <form wire:submit="sendCode" class="space-y-4">
            <div>
                <label class="label" for="mobile">شماره موبایل</label>
                <input wire:model="mobile" id="mobile" class="input text-left" dir="ltr" inputmode="numeric" placeholder="09121234567" autofocus>
                @error('mobile') <p class="error">{{ $message }}</p> @enderror
            </div>
            <button class="cursor-pointer btn-primary w-full" wire:loading.attr="disabled">ارسال کد</button>
        </form>
    @else
        <form wire:submit="resetPassword" class="space-y-4">
            <div>
                <label class="label" for="code">کد ۵ رقمی</label>
                <input wire:model="code" id="code" class="input text-center tracking-[0.5em]" dir="ltr" inputmode="numeric" maxlength="5" autofocus>
                @error('code') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="password">رمز جدید</label>
                    <input wire:model="password" id="password" type="password" class="input text-left" dir="ltr">
                    @error('password') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="password_confirmation">تکرار رمز</label>
                    <input wire:model="password_confirmation" id="password_confirmation" type="password" class="input text-left" dir="ltr">
                </div>
            </div>
            <button class="cursor-pointer btn-primary w-full">تغییر رمز و ورود</button>
            <button type="button" wire:click="$set('step', 1)" class="cursor-pointer w-full text-sm text-slate-500 hover:text-slate-700">تغییر شماره</button>
        </form>
    @endif
    <p class="mt-6 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}" wire:navigate class="cursor-pointer text-violet-700 hover:underline">بازگشت به ورود</a>
    </p>
</x-auth-card>
