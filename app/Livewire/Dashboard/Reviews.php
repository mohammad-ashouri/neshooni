<?php

namespace App\Livewire\Dashboard;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * نظرات ثبت‌شده روی پیج‌های کاربر و پاسخ صاحب پیج.
 */
#[Title('نظرات پیج‌ها')]
class Reviews extends Component
{
    use WithPagination;

    /** نظری که فرم پاسخش باز است */
    public ?int $replyingTo = null;

    public string $reply = '';

    public function startReply(int $id): void
    {
        $this->replyingTo = $id;
        $this->reply = $this->findReview($id)->reply ?? '';
        $this->resetErrorBag();
    }

    public function saveReply(): void
    {
        $this->validate(['reply' => ['required', 'string', 'min:2', 'max:1000']], [], ['reply' => 'پاسخ']);

        $this->findReview($this->replyingTo)->update(['reply' => trim($this->reply), 'replied_at' => now()]);

        $this->reset('replyingTo', 'reply');
        $this->dispatch('toast', message: 'پاسخ شما ثبت شد.');
    }

    public function deleteReply(int $id): void
    {
        $this->findReview($id)->update(['reply' => null, 'replied_at' => null]);
    }

    /** فقط نظرهای پیج‌های خود کاربر */
    private function findReview(int $id): Review
    {
        return Review::whereIn('listing_id', Auth::user()->listings()->select('id'))->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.dashboard.reviews', [
            'reviews' => Review::with(['user', 'listing'])
                ->whereIn('listing_id', Auth::user()->listings()->select('id'))
                ->latest()
                ->paginate(10),
        ]);
    }
}
