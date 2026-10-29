{{--
    پنجره مودال. باز و بسته بودن را خود کامپوننت Livewire با یک متغیر کنترل می‌کند:
    @if ($showReport)
        <x-modal title="گزارش تخلف" close="$set('showReport', false)"> ... </x-modal>
    @endif
--}}
@props(['title' => null, 'close', 'width' => 'max-w-lg'])

<div class="fixed inset-0 z-[80] grid place-items-center overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm" wire:click.self="{{ $close }}" @keydown.escape.window="$wire.{{ $close }}">
    <div {{ $attributes->class(['card w-full p-6 shadow-2xl', $width]) }}>
        @if ($title)
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold">{{ $title }}</h2>
                <button type="button" wire:click="{{ $close }}" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100" aria-label="بستن">✕</button>
            </div>
        @endif
        {{ $slot }}
    </div>
</div>
