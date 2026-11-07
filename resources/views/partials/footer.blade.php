@php($enamad = App\Support\Enamad::code())
<footer class="mt-12 border-t border-slate-200 bg-surface">
    {{-- دسکتاپ چهار ستون: معرفی · دسته‌بندی‌ها · دسترسی سریع · اینماد (بدون کد اینماد، معرفی دو ستون می‌گیرد) --}}
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 md:grid-cols-4">
        <div @class(['md:col-span-2' => ! $enamad])>
            <a href="{{ route('home') }}" wire:navigate class="cursor-pointer inline-flex" aria-label="{{ config('site.name') }}">
                <x-logo tagline :size="44" />
            </a>
            <p class="mt-3 max-w-md text-sm leading-7 text-slate-500">
                نشونی ویترین آنلاین پیج‌ها، کانال‌ها و سایت‌های فروشگاهی کسب‌وکارهای ایرانی در شبکه‌های اجتماعی، پیام‌رسان‌ها و وب است.
                کسب‌وکار خود را رایگان ثبت کنید و دیده شوید.
            </p>
        </div>
        <div>
            <h3 class="font-bold">دسته‌بندی‌های محبوب</h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-500">
                @foreach (App\Models\Category::roots()->take(5) as $category)
                    <li><a href="{{ $category->url() }}" wire:navigate class="cursor-pointer hover:text-violet-700">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h3 class="font-bold">دسترسی سریع</h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-500">
                <li><a href="{{ route('explore') }}" wire:navigate class="cursor-pointer hover:text-violet-700">همه پیج‌ها</a></li>
                <li><a href="{{ route('about') }}" wire:navigate class="cursor-pointer hover:text-violet-700">درباره ما</a></li>
                <li><a href="{{ route('contact') }}" wire:navigate class="cursor-pointer hover:text-violet-700">تماس با ما</a></li>
                <li><a href="{{ route('help') }}" wire:navigate class="cursor-pointer hover:text-violet-700">راهنمای استفاده</a></li>
                <li><a href="{{ auth()->check() ? route('dashboard.listings.create') : route('register') }}" wire:navigate class="cursor-pointer hover:text-violet-700">ثبت پیج</a></li>
                <li><a href="{{ auth()->check() ? route('dashboard.websites.create') : route('register', ['intent' => 'website']) }}" wire:navigate class="cursor-pointer hover:text-violet-700">ثبت سایت فروشگاهی</a></li>
            </ul>
        </div>
        @if ($enamad)
            {{-- کد اینماد از پنل «نماد اینماد» عوض می‌شود (App\Support\Enamad) --}}
            <div>
                <h3 class="font-bold">نماد اعتماد</h3>
                <div class="mt-3 inline-grid size-28 place-items-center overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 [&_img]:max-h-full [&_img]:max-w-full">{!! $enamad !!}</div>
            </div>
        @endif
    </div>
    <div class="border-t border-slate-100 py-4 text-center text-xs text-slate-400">© {{ fa_date(now(), 'y') }} {{ config('site.name') }} — همه حقوق محفوظ است.</div>
</footer>
