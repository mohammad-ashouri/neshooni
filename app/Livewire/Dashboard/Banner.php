<?php

namespace App\Livewire\Dashboard;

use App\Models\Listing;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * ساخت بنر «ما در نشونی هستیم» برای استوری، پست و سایت.
 * کشیدن تصویر در مرورگر انجام می‌شود (resources/js/banner.js)؛
 * اینجا فقط اطلاعات پیج و انتخاب‌های کاربر آماده و به canvas داده می‌شود.
 */
#[Title('بنر و نشان سایت')]
class Banner extends Component
{
    public ?int $listingId = null;

    public string $format = 'story';

    public string $theme = 'violet';

    public function mount(?Listing $listing = null): void
    {
        if ($listing?->exists) {
            abort_unless($listing->user_id === Auth::id(), 403);
            $this->listingId = $listing->id;
        } else {
            $this->listingId = Auth::user()->listings()->approved()->value('id');
        }
    }

    public function render()
    {
        $listings = Auth::user()->listings()->approved()->with('platform', 'category')->get();
        $listing = $listings->firstWhere('id', $this->listingId);

        $banner = null;
        if ($listing) {
            $reviews = $listing->approvedReviews();
            $rating = (float) $reviews->avg('rating');
            $count = $reviews->count();

            $banner = [
                'format' => $this->format,
                'theme' => $this->theme,
                'title' => $listing->title,
                'handle' => $listing->displayHandle(),
                'meta' => trim($listing->platform->label.' · '.$listing->category?->name.($listing->city ? ' · '.$listing->city : '')),
                'rating' => $rating,
                'ratingLine' => $count ? fa_number($rating, 1).' از ۵ · '.fa_number($count).' نظر' : 'اولین نظر را شما بدهید',
                'verified' => Auth::user()->isVerified(),
                'url' => $listing->publicUrl(),
                'image' => $listing->imageUrl() ? url($listing->imageUrl()) : null,
            ];
        }

        return view('livewire.dashboard.banner', compact('listings', 'listing', 'banner'));
    }
}
