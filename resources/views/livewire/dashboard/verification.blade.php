<x-dashboard-shell title="نشان تأیید کسب‌وکار">
    @if ($user->isVerified())
        <div class="card p-10 text-center">
            <x-verified-badge class="mx-auto size-16" />
            <h2 class="mt-4 text-xl font-extrabold">کسب‌وکار شما تأییدشده است</h2>
            <p class="mt-2 text-slate-500">تیک آبی از {{ fa_date($user->verified_at) }} کنار نام شما و همه پیج‌هایتان نمایش داده می‌شود.</p>
        </div>
    @elseif ($unpaid)
        <div class="card p-8 text-center">
            <div class="text-4xl">💳</div>
            <h2 class="mt-3 font-bold">درخواست شما ثبت شده و منتظر پرداخت است</h2>
            <p class="mt-1 text-sm text-slate-500">بعد از پرداخت {{ fa_price($unpaid->amount) }}، مدارک شما بررسی می‌شود.</p>
            <a href="{{ route('checkout', $unpaid) }}" wire:navigate class="btn-primary mt-5">ادامه پرداخت</a>
        </div>
    @elseif ($latest?->status === App\Enums\ModerationStatus::Pending)
        <div class="card p-8 text-center">
            <div class="text-4xl">⏳</div>
            <h2 class="mt-3 font-bold">مدارک شما در حال بررسی است</h2>
            <p class="mt-1 text-sm text-slate-500">ثبت درخواست: {{ fa_date($latest->created_at) }}. نتیجه از طریق اعلان به شما خبر داده می‌شود.</p>
        </div>
    @else
        <div class="grid gap-6 xl:grid-cols-[1fr_20rem]">
            <form wire:submit="submit" class="card space-y-5 p-6">
                @if ($latest?->status === App\Enums\ModerationStatus::Rejected)
                    <p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">درخواست قبلی رد شد: {{ $latest->reason ?? 'مدارک کافی نبود' }}. می‌توانید دوباره درخواست دهید.</p>
                @endif

                <div>
                    <label class="label">نوع کسب‌وکار</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach (['individual' => 'شخص حقیقی', 'company' => 'شرکت (حقوقی)'] as $value => $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-sm has-checked:border-violet-500 has-checked:bg-violet-50">
                                <input type="radio" wire:model.live="business_type" value="{{ $value }}" class="accent-violet-600"> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="label">{{ $business_type === 'company' ? 'روزنامه رسمی یا گواهی ثبت شرکت' : 'جواز کسب یا کارت ملی' }}</label>
                    <input type="file" wire:model="document" accept=".jpg,.jpeg,.png,.pdf" class="input file:me-3 file:rounded-lg file:border-0 file:bg-violet-50 file:px-3 file:py-1 file:text-violet-700">
                    <div wire:loading wire:target="document" class="mt-1 text-xs text-violet-600">در حال بارگذاری...</div>
                    @error('document') <p class="error">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-400">JPG، PNG یا PDF تا ۴ مگابایت. مدارک فقط برای مدیر قابل مشاهده است.</p>
                </div>

                <div>
                    <label class="label">توضیحات (اختیاری)</label>
                    <textarea wire:model="note" rows="3" class="input"></textarea>
                </div>

                <button class="btn-primary" wire:loading.attr="disabled">ثبت درخواست و پرداخت {{ fa_price(config('site.verification_price')) }}</button>
            </form>

            <div class="card h-fit p-6 text-sm leading-8 text-slate-600">
                <x-verified-badge class="size-10" />
                <h2 class="mt-3 font-bold text-slate-800">چرا نشان تأیید؟</h2>
                <ul class="mt-2 list-inside list-disc">
                    <li>تیک آبی کنار نام شما و همه پیج‌ها</li>
                    <li>اعتماد بیشتر خریداران</li>
                    <li>نمایش روی بنر «ما در نشونی هستیم»</li>
                </ul>
                <p class="mt-3 text-xs text-slate-400">هزینه یک‌باره است و در صورت رد درخواست، می‌توانید با مدارک کامل دوباره اقدام کنید.</p>
            </div>
        </div>
    @endif
</x-dashboard-shell>
