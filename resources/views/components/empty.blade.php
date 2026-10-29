{{-- حالت خالی لیست‌ها --}}
@props(['icon' => '📭', 'title' => 'موردی پیدا نشد'])

<div {{ $attributes->class('card px-6 py-14 text-center') }}>
    <div class="text-4xl">{{ $icon }}</div>
    <h3 class="mt-3 font-bold text-slate-700">{{ $title }}</h3>
    @if ($slot->isNotEmpty())
        <div class="mt-2 text-sm text-slate-500">{{ $slot }}</div>
    @endif
</div>
