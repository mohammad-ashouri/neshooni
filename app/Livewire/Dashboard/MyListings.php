<?php

namespace App\Livewire\Dashboard;

use App\Services\ListingSeoAudit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('پیج‌های من')]
class MyListings extends Component
{
    public function delete(int $id): void
    {
        $listing = Auth::user()->listings()->with('photos')->findOrFail($id);

        // فایل‌های تصویر هم پاک می‌شوند
        Storage::disk('public')->delete(array_filter([$listing->image, ...$listing->photos->pluck('path')]));
        $listing->delete();

        $this->dispatch('toast', message: 'پیج حذف شد.');
    }

    public function render(ListingSeoAudit $audit)
    {
        $listings = Auth::user()->listings()
            ->with(['platform', 'category', 'seo'])
            ->withCount(['fans', 'approvedReviews as reviews_count', 'photos as pending_photos_count' => fn ($q) => $q->where('status', 'pending')])
            ->latest()
            ->get();

        return view('livewire.dashboard.my-listings', [
            'listings' => $listings,
            // امتیاز سئوی هر پیج برای نمایش کنار دکمه «سئو»
            'seoScores' => $listings->mapWithKeys(fn ($listing) => [$listing->id => $audit->run($listing)['score']]),
        ]);
    }
}
