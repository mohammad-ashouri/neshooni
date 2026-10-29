<x-dashboard-shell title="پروفایل">
    <div class="grid gap-6 xl:grid-cols-[1fr_20rem]">
        <form wire:submit="save" class="card space-y-5 p-6">
            <div class="flex items-center gap-4">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" class="size-20 rounded-full object-cover">
                @else
                    <x-avatar :src="auth()->user()->avatarUrl()" :name="$name" :seed="auth()->id()" class="size-20 text-3xl" rounded="rounded-full" />
                @endif
                <label class="btn-light cursor-pointer">
                    تغییر تصویر
                    <input type="file" wire:model="avatar" accept="image/*" class="sr-only">
                </label>
                @error('avatar') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label">نام کسب‌وکار یا نام شما</label>
                    <input wire:model="name" class="input">
                    @error('name') <p class="error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">شماره موبایل</label>
                    <input value="{{ auth()->user()->mobile }}" class="input text-left" dir="ltr" disabled>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="label">کشور</label>
                    <select wire:model.live="country" class="input">
                        @foreach (config('locations') as $code => $item)
                            <option value="{{ $code }}">{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">استان</label>
                    <select wire:model.live="province" class="input">
                        <option value="">انتخاب کنید</option>
                        @foreach ($provinces as $item)
                            <option>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">شهر</label>
                    <select wire:model="city" class="input" @disabled(! $province)>
                        <option value="">انتخاب کنید</option>
                        @foreach ($cities as $item)
                            <option>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="label">معرفی کوتاه</label>
                <textarea wire:model="bio" rows="3" class="input" placeholder="چند خط درباره کسب‌وکار شما (در صفحه عمومی نمایش داده می‌شود)"></textarea>
                @error('bio') <p class="error">{{ $message }}</p> @enderror
            </div>

            <button class="btn-primary">ذخیره پروفایل</button>
        </form>

        <form wire:submit="changePassword" class="card h-fit space-y-4 p-6">
            <h2 class="font-bold">🔒 تغییر رمز عبور</h2>
            <div>
                <label class="label">رمز فعلی</label>
                <input wire:model="current_password" type="password" class="input text-left" dir="ltr">
                @error('current_password') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">رمز جدید</label>
                <input wire:model="password" type="password" class="input text-left" dir="ltr">
                @error('password') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">تکرار رمز جدید</label>
                <input wire:model="password_confirmation" type="password" class="input text-left" dir="ltr">
            </div>
            <button class="btn-light w-full">تغییر رمز</button>
        </form>
    </div>
</x-dashboard-shell>
