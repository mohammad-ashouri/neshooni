{{-- لوگوی پلتفرم؛ اگر لوگو آپلود نشده باشد حرف اختصاری روی رنگ پلتفرم --}}
@props(['platform'])

@if ($platform->logoUrl())
    <img src="{{ $platform->logoUrl() }}" alt="{{ $platform->label }}" title="{{ $platform->label }}" {{ $attributes->class('inline-block shrink-0 rounded-lg bg-white object-cover') }}>
@else
    <span title="{{ $platform->label }}" style="background: {{ $platform->color }}" {{ $attributes->class('inline-grid shrink-0 place-items-center rounded-lg text-[0.6em] font-black text-white') }}>{{ $platform->letter }}</span>
@endif
