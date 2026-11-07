<x-dashboard-shell title="ویژه کردن پیج">
    <div class="card mb-6 flex items-center gap-4 p-5">
        <x-avatar :src="$listing->imageUrl()" :name="$listing->title" :seed="$listing->id" class="size-14 text-xl" />
        <div class="flex-1">
            <div class="font-bold">{{ $listing->title }}</div>
            <div class="text-sm text-slate-500">
                @if ($listing->isFeatured())
                    ⭐ ویژه تا {{ fa_date($listing->featured_until) }} — خرید جدید به همین تاریخ اضافه می‌شود.
                @else
                    این پیج در حال حاضر ویژه نیست.
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        @foreach ($plans as $plan)
            <div @class(['card relative flex flex-col p-6 text-center', 'border-violet-400 ring-2 ring-violet-200' => $plan['popular'] ?? false])>
                @if ($plan['popular'] ?? false)
                    <span class="chip absolute -top-3 left-1/2 -translate-x-1/2 bg-violet-600 text-white">پیشنهاد ما</span>
                @endif
                <h2 class="text-lg font-bold">{{ $plan['label'] }}</h2>
                <div class="mt-3 text-3xl font-black">{{ fa_number($plan['price']) }}</div>
                <div class="text-sm text-slate-500">تومان · {{ fa_number($plan['days']) }} روز</div>
                <button wire:click="buy({{ $plan['days'] }})" class="cursor-pointer btn-primary mt-6">انتخاب و پرداخت</button>
            </div>
        @endforeach
    </div>

    <div class="card mt-6 p-6 text-sm leading-8 text-slate-600">
        <h2 class="mb-2 font-bold text-slate-800">آگهی ویژه چه مزیتی دارد؟</h2>
        <ul class="list-inside list-disc">
            <li>نمایش در بخش «آگهی‌های ویژه» صفحه اول</li>
            <li>نمایش در ردیف «آگهی ویژه» بالای نتایج جستجو و صفحه دسته‌هایی که پیج شما در آن است</li>
            <li>قاب طلایی و برچسب «آگهی ویژه» روی کارت پیج در این جایگاه‌ها</li>
        </ul>
        <p class="mt-2 text-xs text-slate-500">رتبه پیج در نتایج عادی به پرداخت ربطی ندارد و بر اساس مرتب‌سازی انتخابی بازدیدکننده (جدیدترین، امتیاز، بازدید و...) است؛ این شفافیت اعتماد بازدیدکننده‌ها به آگهی شما را هم بیشتر می‌کند.</p>
    </div>
</x-dashboard-shell>
