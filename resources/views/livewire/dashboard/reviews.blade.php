<x-dashboard-shell title="نظرات پیج‌های شما">
    @if ($reviews->isEmpty())
        <x-empty icon="💬" title="هنوز نظری روی پیج‌های شما ثبت نشده" />
    @else
        <div class="space-y-3">
            @foreach ($reviews as $review)
                <article class="card p-5" wire:key="review-{{ $review->id }}">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-avatar :src="$review->user->avatarUrl()" :name="$review->user->name" :seed="$review->user_id" class="size-9 text-sm" rounded="rounded-full" />
                        <div class="flex-1">
                            <div class="text-sm font-semibold">{{ $review->user->name }} <span class="font-normal text-slate-400">درباره</span> <a href="{{ route('listings.show', $review->listing) }}" wire:navigate class="text-violet-700 hover:underline">{{ $review->listing->title }}</a></div>
                            <div class="text-xs text-slate-400">{{ fa_datetime($review->created_at) }}</div>
                        </div>
                        <x-stars :rating="$review->rating" class="text-sm" />
                        <x-status-badge :status="$review->status" />
                    </div>
                    <p class="mt-3 text-sm leading-7">{{ $review->body }}</p>

                    @if ($replyingTo === $review->id)
                        <form wire:submit="saveReply" class="mt-3 space-y-2">
                            <textarea wire:model="reply" rows="3" class="input" placeholder="پاسخ شما به این نظر (برای همه نمایش داده می‌شود)" autofocus></textarea>
                            @error('reply') <p class="error">{{ $message }}</p> @enderror
                            <div class="flex gap-2">
                                <button class="btn-primary py-2">ثبت پاسخ</button>
                                <button type="button" wire:click="$set('replyingTo', null)" class="btn-light py-2">انصراف</button>
                            </div>
                        </form>
                    @elseif ($review->reply)
                        <div class="mt-3 rounded-xl border-r-4 border-violet-400 bg-violet-50 px-4 py-3 text-sm">
                            <div class="mb-1 text-xs font-bold text-violet-700">پاسخ شما · {{ fa_date($review->replied_at) }}</div>
                            <p class="leading-7">{{ $review->reply }}</p>
                            <div class="mt-2 flex gap-3 text-xs">
                                <button wire:click="startReply({{ $review->id }})" class="text-violet-700 hover:underline">ویرایش</button>
                                <button wire:click="deleteReply({{ $review->id }})" wire:confirm="پاسخ حذف شود؟" class="text-rose-600 hover:underline">حذف</button>
                            </div>
                        </div>
                    @else
                        <button wire:click="startReply({{ $review->id }})" class="btn-light mt-3 py-2 text-xs">↩️ پاسخ دادن</button>
                    @endif

                    @if ($review->status === App\Enums\ModerationStatus::Pending)
                        <p class="mt-2 text-xs text-amber-700">این نظر هنوز تأیید نشده و برای دیگران نمایش داده نمی‌شود.</p>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $reviews->links() }}</div>
    @endif
</x-dashboard-shell>
