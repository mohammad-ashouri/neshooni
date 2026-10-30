{{-- تصویر پروفایل؛ اگر عکسی نباشد حرف اول نام روی یک گرادیانت رنگی نمایش داده می‌شود --}}
@props(['src' => null, 'name' => '؟', 'seed' => 0, 'rounded' => 'rounded-2xl'])

@php
    $gradients = ['from-violet-500 to-fuchsia-500', 'from-sky-500 to-indigo-500', 'from-emerald-500 to-teal-500', 'from-amber-500 to-orange-500', 'from-rose-500 to-pink-500', 'from-cyan-500 to-blue-500'];
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}" loading="lazy" decoding="async" {{ $attributes->class([$rounded, 'shrink-0 object-cover']) }}>
@else
    <div {{ $attributes->class([$rounded, 'grid shrink-0 place-items-center bg-linear-to-br font-black text-white', $gradients[$seed % count($gradients)]]) }}>
        {{ mb_substr(trim($name), 0, 1) ?: '؟' }}
    </div>
@endif
