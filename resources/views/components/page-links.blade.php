{{--
    صفحه‌بندی با لینک واقعی (<a href="?page=2">) تا موتور جستجو هم صفحه‌های بعدی را ببیند؛
    wire:navigate باعث می‌شود برای کاربر بدون بارگذاری کامل صفحه عوض شود.
    $url تابعی است که شماره صفحه را می‌گیرد و آدرس کامل (با فیلترها) را برمی‌گرداند.
--}}
@props(['paginator', 'url'])

@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        // صفحه اول، آخر و دو صفحه دور صفحه فعلی؛ بقیه با «…»
        $pages = collect(range(1, $last))->filter(fn ($p) => $p === 1 || $p === $last || abs($p - $current) <= 2)->values();
    @endphp

    <nav {{ $attributes->class('flex flex-wrap items-center justify-center gap-1.5 text-sm') }} aria-label="صفحه‌بندی">
        @if ($current > 1)
            <a href="{{ $url($current - 1) }}" wire:navigate rel="prev" class="btn-light px-3 py-2">قبلی</a>
        @endif

        @foreach ($pages as $i => $page)
            @if ($i > 0 && $page - $pages[$i - 1] > 1)
                <span class="px-1 text-slate-400">…</span>
            @endif
            @if ($page === $current)
                <span aria-current="page" class="grid size-10 place-items-center rounded-xl bg-violet-600 font-bold text-white">{{ fa_number($page) }}</span>
            @else
                <a href="{{ $url($page) }}" wire:navigate class="grid size-10 place-items-center rounded-xl border border-slate-200 bg-surface hover:bg-slate-50">{{ fa_number($page) }}</a>
            @endif
        @endforeach

        @if ($current < $last)
            <a href="{{ $url($current + 1) }}" wire:navigate rel="next" class="btn-light px-3 py-2">بعدی</a>
        @endif
    </nav>
@endif
