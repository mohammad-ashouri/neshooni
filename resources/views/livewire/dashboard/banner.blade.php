<x-dashboard-shell title="بنر «ما در نشونی هستیم»">
    @if (! $listing)
        <x-empty icon="🖼️" title="پیج تأییدشده‌ای ندارید">
            <p>بعد از تأیید اولین پیج، می‌توانید برایش بنر بسازید.</p>
        </x-empty>
    @else
        @php
            $bioText = "ما را در نشونی ببینید و نظر بدهید 👇\n".$listing->publicUrl();
            $embed = '<a href="'.$listing->publicUrl().'" target="_blank" rel="noopener">'.$listing->title.' در نشونی</a>';
        @endphp

        <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
            <div class="card grid place-items-center bg-slate-50 p-6">
                {{-- اطلاعات بنر به صورت JSON روی canvas؛ banner.js آن را می‌کشد --}}
                <canvas data-banner="{{ json_encode($banner) }}" wire:key="banner-{{ $listing->id }}-{{ $format }}-{{ $theme }}"
                        @class(['h-auto rounded-xl shadow-xl', 'max-h-[70vh] w-auto' => $format === 'story', 'w-full max-w-md' => $format === 'post', 'w-full max-w-xl' => $format === 'badge'])></canvas>
            </div>

            <div class="space-y-4">
                <div class="card space-y-4 p-5">
                    @if ($listings->count() > 1)
                        <div>
                            <label class="label">پیج</label>
                            <select wire:model.live="listingId" class="input">
                                @foreach ($listings as $item)
                                    <option value="{{ $item->id }}">{{ $item->title }} ({{ $item->platform->label }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="label">قالب</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (['story' => 'استوری', 'post' => 'پست', 'badge' => 'نشان سایت'] as $value => $label)
                                <button wire:click="$set('format', '{{ $value }}')" @class(['cursor-pointer filter-chip py-2', 'filter-chip-active' => $format === $value])>{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="label">رنگ</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (['violet' => 'بنفش', 'dark' => 'تیره', 'light' => 'روشن'] as $value => $label)
                                <button wire:click="$set('theme', '{{ $value }}')" @class(['cursor-pointer filter-chip py-2', 'filter-chip-active' => $theme === $value])>{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                    <button onclick="downloadBanner(document.querySelector('canvas[data-banner]'), 'neshooni-{{ $format }}.png')" class="cursor-pointer btn-primary w-full">⬇️ دانلود تصویر PNG</button>
                </div>

                <div class="card space-y-2 p-5">
                    <h2 class="text-sm font-bold">متن پیشنهادی بیو یا کپشن</h2>
                    <textarea readonly rows="3" class="input text-sm">{{ $bioText }}</textarea>
                    <button x-on:click="navigator.clipboard.writeText(@js($bioText)); $dispatch('toast', { message: 'متن کپی شد' })" class="cursor-pointer btn-light w-full py-2 text-xs">📋 کپی متن</button>
                </div>

                <div class="card space-y-2 p-5">
                    <h2 class="text-sm font-bold">کد لینک برای سایت شما</h2>
                    <textarea readonly rows="3" class="input text-left text-xs" dir="ltr">{{ $embed }}</textarea>
                    <button x-on:click="navigator.clipboard.writeText(@js($embed)); $dispatch('toast', { message: 'کد کپی شد' })" class="cursor-pointer btn-light w-full py-2 text-xs">📋 کپی کد</button>
                </div>
            </div>
        </div>
    @endif
</x-dashboard-shell>
