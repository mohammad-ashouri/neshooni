{{-- پنج ستاره امتیاز (فقط نمایش) --}}
@props(['rating' => 0])

<span {{ $attributes->class('inline-flex text-amber-400') }} aria-label="امتیاز {{ fa_number($rating, 1) }} از ۵">
    @for ($i = 1; $i <= 5; $i++)
        <span @class(['text-slate-300' => $rating < $i - 0.25])>★</span>
    @endfor
</span>
