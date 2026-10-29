{{--
    نمودار میله‌ای افقی: $items = [['label' => '...', 'value' => 12, 'hint' => '...'], ...]
--}}
@props(['items'])

@php($max = max(1, ...array_column($items, 'value')))

<div {{ $attributes->class('space-y-3') }}>
    @foreach ($items as $item)
        <div title="{{ $item['hint'] ?? '' }}">
            <div class="mb-1 flex justify-between gap-2 text-xs">
                <span class="truncate text-slate-600">{{ $item['label'] }}</span>
                <span class="font-semibold text-slate-700">{{ fa_number($item['value']) }}</span>
            </div>
            <div class="h-2 rounded-full bg-slate-100">
                <div class="h-2 rounded-full bg-(--color-series-1)" style="width: {{ $item['value'] / $max * 100 }}%"></div>
            </div>
        </div>
    @endforeach
</div>
