<x-auth-card title="ثبت‌نام رایگان" subtitle="در کمتر از یک دقیقه حساب بسازید و پیج خود را معرفی کنید">
    <form wire:submit="register" class="space-y-4">
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
        <button class="btn-primary w-full" wire:loading.attr="disabled">ساخت حساب</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">
        قبلاً ثبت‌نام کرده‌اید؟ <a href="{{ route('login') }}" wire:navigate class="font-semibold text-violet-700 hover:underline">وارد شوید</a>
    </p>
</x-auth-card>
