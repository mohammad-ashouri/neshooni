<?php

namespace App\Livewire\Admin;

use App\Enums\ModerationStatus;
use App\Models\Review;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::admin'), Title('مدیریت نظرات')]
class Reviews extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function setStatus(int $id, string $status): void
    {
        Review::findOrFail($id)->update(['status' => ModerationStatus::from($status)]);
        $this->dispatch('toast', message: $status === 'approved' ? 'نظر تأیید شد.' : 'نظر رد شد.');
    }

    public function delete(int $id): void
    {
        Gate::authorize('reviews.delete');
        Review::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'نظر حذف شد.');
    }

    /** حذف پاسخ نامناسب صاحب پیج */
    public function deleteReply(int $id): void
    {
        Gate::authorize('reviews.delete');
        Review::findOrFail($id)->update(['reply' => null, 'replied_at' => null]);
        $this->dispatch('toast', message: 'پاسخ حذف شد.');
    }

    public function render()
    {
        return view('livewire.admin.reviews', [
            'reviews' => Review::with(['user', 'listing'])->where('status', $this->status)->latest()->paginate(15),
            'counts' => Review::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}
