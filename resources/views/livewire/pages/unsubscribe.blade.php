<div class="mx-auto max-w-lg px-4 py-16">
    <div class="card p-6 text-center">
        @if ($done)
            <div class="text-4xl">✅</div>
            <h1 class="mt-3 text-xl font-extrabold">عضویت شما لغو شد</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">از این به بعد پیام خبرنامه‌ای از نشونی دریافت نمی‌کنید. هر وقت خواستید می‌توانید دوباره عضو شوید.</p>
            <a href="{{ route('home') }}" wire:navigate class="cursor-pointer btn-light mt-5 inline-block">بازگشت به نشونی</a>
        @elseif ($subscriptions->isEmpty())
            <div class="text-4xl">📭</div>
            <h1 class="mt-3 text-xl font-extrabold">اشتراکی پیدا نشد</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">این لینک قبلاً استفاده شده یا عضویت از قبل لغو شده است.</p>
            <a href="{{ route('home') }}" wire:navigate class="cursor-pointer btn-light mt-5 inline-block">بازگشت به نشونی</a>
        @else
            <div class="text-4xl">📬</div>
            <h1 class="mt-3 text-xl font-extrabold">لغو عضویت خبرنامه</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">شما در این خبرنامه‌ها عضو هستید:</p>
            <ul class="mt-3 flex flex-wrap justify-center gap-2">
                @foreach ($subscriptions as $subscription)
                    <li class="chip bg-violet-50 text-violet-700">{{ $subscription->category?->name ?? 'خبرنامه عمومی' }}</li>
                @endforeach
            </ul>
            <button wire:click="unsubscribe" class="cursor-pointer btn-danger mt-6 w-full">لغو همه عضویت‌ها</button>
            <p class="mt-3 text-xs text-slate-400">اطلاعات تماس شما به هیچ فروشگاهی داده نمی‌شود.</p>
        @endif
    </div>
</div>
