<div>
    <h1 class="mb-5 text-2xl font-extrabold">نظرات</h1>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach (App\Enums\ModerationStatus::cases() as $case)
            <button wire:click="$set('status', '{{ $case->value }}')" @class(['cursor-pointer filter-chip', 'filter-chip-active' => $status === $case->value])>
                {{ $case->label() }} ({{ fa_number($counts[$case->value] ?? 0) }})
            </button>
        @endforeach
    </div>

    @if ($reviews->isEmpty())
        <x-empty icon="💬" title="نظری در این بخش نیست" />
    @else
        <div class="space-y-3">
            @foreach ($reviews as $review)
                <article class="card p-5" wire:key="admin-review-{{ $review->id }}">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex-1 text-sm">
                            <span class="font-semibold">{{ $review->user->name }}</span>
                            <span class="text-slate-400">درباره</span>
                            <a href="{{ route('listings.show', $review->listing) }}" wire:navigate class="cursor-pointer text-violet-700 hover:underline">{{ $review->listing->title }}</a>
                            <div class="text-xs text-slate-400">{{ fa_datetime($review->created_at) }}</div>
                        </div>
                        <x-stars :rating="$review->rating" />
                    </div>
                    <p class="mt-3 text-sm leading-7">{{ $review->body }}</p>

                    @if ($review->reply)
                        <div class="mt-3 rounded-xl bg-violet-50 px-4 py-2 text-sm">
                            <span class="text-xs font-bold text-violet-700">پاسخ صاحب پیج:</span> {{ $review->reply }}
                            @can('reviews.delete')
                                <button wire:click="deleteReply({{ $review->id }})" wire:confirm="پاسخ حذف شود؟" class="cursor-pointer ms-2 text-xs text-rose-600 hover:underline">حذف پاسخ</button>
                            @endcan
                        </div>
                    @endif

                    <div class="mt-3 flex flex-wrap gap-2">
                        @if ($status !== 'approved')
                            <button wire:click="setStatus({{ $review->id }}, 'approved')" class="cursor-pointer btn-success py-1.5 text-xs">تأیید</button>
                        @endif
                        @if ($status !== 'rejected')
                            <button wire:click="setStatus({{ $review->id }}, 'rejected')" class="cursor-pointer btn-danger py-1.5 text-xs">رد</button>
                        @endif
                        @can('reviews.delete')
                            <button wire:click="delete({{ $review->id }})" wire:confirm="نظر برای همیشه حذف شود؟" class="cursor-pointer btn-light py-1.5 text-xs text-rose-600">حذف</button>
                        @endcan
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-4">{{ $reviews->links() }}</div>
    @endif
</div>
