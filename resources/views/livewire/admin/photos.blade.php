<div>
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-extrabold">تصاویر در انتظار تأیید</h1>
        @if ($photos->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                <button wire:click="toggleAll" class="btn-light py-2">{{ count($selected) === $photos->count() ? 'لغو انتخاب' : 'انتخاب همه' }}</button>
                @if ($selected)
                    <button wire:click="approve(@js($selected))" class="btn-success py-2">تأیید {{ fa_number(count($selected)) }} مورد</button>
                    <button wire:click="reject(@js($selected))" wire:confirm="تصاویر انتخاب‌شده رد و حذف شوند؟" class="btn-danger py-2">رد</button>
                @endif
            </div>
        @endif
    </div>

    @if ($photos->isEmpty())
        <x-empty icon="🖼️" title="تصویری برای بررسی نیست" />
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
            @foreach ($photos as $photo)
                <div wire:key="photo-{{ $photo->id }}" @class(['card overflow-hidden', 'ring-2 ring-violet-500' => in_array((string) $photo->id, $selected)])>
                    <label class="relative block aspect-square cursor-pointer bg-slate-100">
                        <img src="{{ $photo->url() }}" alt="" class="size-full object-cover" loading="lazy">
                        <input type="checkbox" wire:model.live="selected" value="{{ $photo->id }}" class="absolute top-2 right-2 size-5 accent-violet-600">
                    </label>
                    <div class="p-2.5 text-xs">
                        <a href="{{ route('listings.show', $photo->listing) }}" wire:navigate class="block truncate font-semibold hover:text-violet-700">{{ $photo->listing->title }}</a>
                        <p class="truncate text-slate-500">{{ $photo->caption ?: 'بدون توضیح' }}</p>
                        <div class="mt-2 grid grid-cols-2 gap-1.5">
                            <button wire:click="approve([{{ $photo->id }}])" class="btn-success px-2 py-1.5 text-xs">✓</button>
                            <button wire:click="reject([{{ $photo->id }}])" class="btn-danger px-2 py-1.5 text-xs">✕</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
