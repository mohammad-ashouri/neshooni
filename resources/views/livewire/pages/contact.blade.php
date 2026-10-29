<div class="mx-auto max-w-5xl px-4 py-10">
    <div class="text-center">
        <h1 class="text-3xl font-black">تماس با ما</h1>
        <p class="mt-2 text-slate-500">سؤال، پیشنهاد یا مشکلی دارید؟ خوشحال می‌شویم بشنویم.</p>
    </div>

    <div class="mt-10 grid gap-6 md:grid-cols-[18rem_1fr]">
        <div class="space-y-3">
            @foreach ([['📞', 'تلفن', config('site.contact.phone'), true], ['✉️', 'ایمیل', config('site.contact.email'), true], ['📍', 'آدرس', config('site.contact.address'), false], ['🕘', 'ساعات پاسخگویی', config('site.contact.hours'), false]] as [$icon, $label, $value, $ltr])
                <div class="card flex gap-3 p-4">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-violet-50 text-lg">{{ $icon }}</span>
                    <div>
                        <div class="text-xs text-slate-500">{{ $label }}</div>
                        <div class="mt-0.5 text-sm font-semibold" @if ($ltr) dir="ltr" style="text-align: right" @endif>{{ $ltr ? $value : fa_digits($value) }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card p-6">
            @if ($sent)
                <div class="py-12 text-center">
                    <div class="text-5xl">✅</div>
                    <h2 class="mt-4 text-lg font-bold">پیام شما رسید</h2>
                    <p class="mt-1 text-sm text-slate-500">در اولین فرصت پاسخ می‌دهیم.</p>
                    <button wire:click="$set('sent', false)" class="btn-light mt-6">ارسال پیام دیگر</button>
                </div>
            @else
                <form wire:submit="send" class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label">نام</label>
                            <input wire:model="name" class="input">
                            @error('name') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">موبایل یا ایمیل</label>
                            <input wire:model="contact" class="input text-left" dir="ltr">
                            @error('contact') <p class="error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="label">موضوع</label>
                        <select wire:model="subject" class="input">
                            @foreach (App\Models\ContactMessage::SUBJECTS as $item)
                                <option>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">متن پیام</label>
                        <textarea wire:model="body" rows="6" class="input"></textarea>
                        @error('body') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <button class="btn-primary" wire:loading.attr="disabled">ارسال پیام</button>
                </form>
            @endif
        </div>
    </div>
</div>
