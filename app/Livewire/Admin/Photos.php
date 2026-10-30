<?php

namespace App\Livewire\Admin;

use App\Enums\ModerationStatus;
use App\Models\ListingPhoto;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * صفحه سریع بررسی تصاویر گالری: فقط عکس‌ها، با انتخاب گروهی.
 * عکس رد شده حذف می‌شود تا فضای ذخیره‌سازی پر نشود.
 */
#[Layout('layouts::admin'), Title('بررسی تصاویر')]
class Photos extends Component
{
    /** شناسه عکس‌های انتخاب‌شده */
    public array $selected = [];

    public function approve(array $ids): void
    {
        ListingPhoto::whereIn('id', $ids)->update(['status' => ModerationStatus::Approved]);
        $this->finish(count($ids).' تصویر تأیید شد.');
    }

    public function reject(array $ids): void
    {
        $photos = ListingPhoto::whereIn('id', $ids)->get();
        Storage::disk('public')->delete($photos->pluck('path')->all());
        ListingPhoto::whereIn('id', $ids)->delete();

        $this->finish(count($ids).' تصویر رد و حذف شد.');
    }

    public function toggleAll(): void
    {
        $ids = $this->pendingQuery()->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->selected = count($this->selected) === count($ids) ? [] : $ids;
    }

    private function finish(string $message): void
    {
        $this->selected = [];
        $this->dispatch('toast', message: fa_digits($message));
    }

    private function pendingQuery()
    {
        return ListingPhoto::where('status', ModerationStatus::Pending)->oldest()->limit(60);
    }

    public function render()
    {
        return view('livewire.admin.photos', [
            'photos' => $this->pendingQuery()->with('listing')->get(),
        ]);
    }
}
