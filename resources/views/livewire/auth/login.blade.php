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
