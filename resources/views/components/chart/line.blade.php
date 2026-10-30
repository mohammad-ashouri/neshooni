{{--
    نمودار خطی ساده با SVG (بدون کتابخانه).
    $series = ['labels' => [Carbon...], 'views' => [...], 'clicks' => [...]] از ListingStats::series()
    با رفتن ماوس روی هر نقطه، عدد آن روز در <title> نمایش داده می‌شود.
--}}
@props(['series'])

@php
    $width = 600;
    $height = 200;
    $pad = 8;
    $count = count($series['labels']);
    $max = max([1, ...$series['views'], ...$series['clicks']]);
    $x = fn (int $i) => $count > 1 ? $pad + $i * ($width - 2 * $pad) / ($count - 1) : $width / 2;
    $y = fn (int $value) => $height - $pad - $value / $max * ($height - 2 * $pad);
    $points = fn (array $values) => collect($values)->map(fn ($v, $i) => round($x($i), 1).','.round($y($v), 1))->implode(' ');
    $lines = ['views' => ['بازدید', 'var(--color-series-1)'], 'clicks' => ['کلیک', 'var(--color-series-2)']];
@endphp

<div {{ $attributes }}>
    <div class="mb-3 flex items-center gap-4 text-xs text-slate-500">
        @foreach ($lines as [$label, $color])
            <span class="flex items-center gap-1.5"><span class="h-0.5 w-4 rounded" style="background: {{ $color }}"></span>{{ $label }}</span>
        @endforeach
    </div>
    <svg viewBox="0 0 {{ $width }} {{ $height }}" class="h-48 w-full overflow-visible" preserveAspectRatio="none">
        @foreach ([0.25, 0.5, 0.75, 1] as $line)
            <line x1="0" x2="{{ $width }}" y1="{{ $y((int) round($max * $line)) }}" y2="{{ $y((int) round($max * $line)) }}" stroke="currentColor" class="text-slate-100" vector-effect="non-scaling-stroke" />
        @endforeach
        @foreach ($lines as $key => [$label, $color])
            <polyline points="{{ $points($series[$key]) }}" fill="none" stroke="{{ $color }}" stroke-width="2" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
        @endforeach
        {{-- ستون‌های نامرئی برای راهنمای هر روز --}}
        @foreach ($series['labels'] as $i => $date)
            <rect x="{{ $x($i) - ($width / max($count, 1)) / 2 }}" y="0" width="{{ $width / max($count, 1) }}" height="{{ $height }}" fill="transparent" class="hover:fill-slate-500/10">
                <title>{{ fa_date($date, 'EEEE d MMMM') }}: {{ fa_number($series['views'][$i]) }} بازدید، {{ fa_number($series['clicks'][$i]) }} کلیک</title>
            </rect>
        @endforeach
    </svg>
    <div class="mt-2 flex justify-between text-[11px] text-slate-400">
        <span>{{ fa_date($series['labels'][0], 'd MMMM') }}</span>
        <span>{{ fa_date(end($series['labels']), 'd MMMM') }}</span>
    </div>
</div>
