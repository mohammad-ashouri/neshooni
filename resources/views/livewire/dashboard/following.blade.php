<x-dashboard-shell title="پیج‌هایی که دنبال می‌کنید">
    @if ($listings->isEmpty())
        <x-empty icon="❤️" title="هنوز پیجی را دنبال نکرده‌اید">
            <p>در صفحه هر پیج روی «دنبال کردن» بزنید تا اینجا جمع شوند.</p>
            <a href="{{ route('explore') }}" wire:navigate class="cursor-pointer btn-primary mt-4">گشتن در پیج‌ها</a>
        </x-empty>
    @else
        <div class="grid gap-4 pt-2 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($listings as $listing)
                <div class="relative" wire:key="follow-{{ $listing->id }}">
                    <x-listing-card :listing="$listing" />
                    <button wire:click="unfollow({{ $listing->id }})" class="cursor-pointer absolute top-3 left-3 rounded-lg bg-surface/90 px-2 py-1 text-xs text-rose-600 shadow-sm hover:bg-rose-50">💔 لغو</button>
                </div>
            @endforeach
        </div>
    @endif
</x-dashboard-shell>
