{{-- راهنما؛ در سایت با قالب اصلی و در پنل با قالب پنل نمایش داده می‌شود --}}
<x-dynamic-component :component="'layouts::'.$layout" :title="$title">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <article class="card guide p-6 sm:p-10">
            {!! $html !!}
        </article>
    </div>
</x-dynamic-component>
