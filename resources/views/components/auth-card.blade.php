{{-- قاب مشترک صفحه‌های ورود، ثبت‌نام و بازیابی رمز --}}
@props(['title', 'subtitle' => null])

<div class="mx-auto grid min-h-[70vh] max-w-md place-items-center px-4 py-12">
    <div class="card w-full p-6 shadow-xl shadow-violet-500/5 sm:p-8">
        <div class="mb-6 text-center">
            <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-linear-to-br from-violet-600 to-fuchsia-500 text-2xl font-black text-white">ن</span>
            <h1 class="mt-4 text-xl font-extrabold">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
        {{ $slot }}
    </div>
</div>
