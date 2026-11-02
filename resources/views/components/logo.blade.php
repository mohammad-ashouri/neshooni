{{-- لوگوی نشونی: نشان فروشگاه + نام (و در صورت نیاز شعار). فایل‌های لوگو در public/images هستند --}}
{{-- اندازه نشان با width/height خود تگ تعیین می‌شود تا حتی بدون CSS هم از هدر بیرون نزند --}}
@props(['tagline' => false, 'size' => 36])

<span {{ $attributes->class('flex items-center gap-2') }}>
    <img src="/images/logo-mark.svg" alt="" width="{{ round($size * 254 / 300) }}" height="{{ $size }}" class="shrink-0">
    <span class="flex flex-col">
        <span class="bg-linear-to-l from-violet-600 to-indigo-950 bg-clip-text pb-1 text-xl leading-none font-black text-transparent dark:to-violet-300">{{ config('site.name') }}</span>
        @if ($tagline)
            <span class="text-xs text-slate-500">{{ config('site.tagline') }}</span>
        @endif
    </span>
</span>
