{{-- قاب مشترک صفحه‌های ورود، ثبت‌نام و بازیابی رمز --}}
@props(['title', 'subtitle' => null])

<div class="mx-auto grid min-h-[70vh] max-w-md place-items-center px-4 py-12">
    <div class="card w-full p-6 shadow-xl shadow-violet-500/5 sm:p-8">
        <div class="mb-6 text-center">
            <a href="{{ route('home') }}" wire:navigate class="cursor-pointer inline-block" aria-label="{{ config('site.name') }}">
                <img src="/images/logo.svg" alt="" width="59" height="64">
            </a>
            <h1 class="mt-4 text-xl font-extrabold">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
        {{ $slot }}
    </div>
</div>
