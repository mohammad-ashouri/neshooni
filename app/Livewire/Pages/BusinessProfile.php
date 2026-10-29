<?php

namespace App\Livewire\Pages;

use App\Models\Review;
use App\Models\User;
use Livewire\Component;

/**
 * صفحه عمومی یک کسب‌وکار با همه پیج‌های تأییدشده‌اش.
 */
class BusinessProfile extends Component
{
    public User $user;

    public function render()
    {
        $listings = $this->user->listings()->approved()->forCards()->latest()->get();

        return view('livewire.pages.business-profile', [
            'listings' => $listings,
            'followers' => $listings->sum(fn ($l) => $l->followers),
            'rating' => Review::approved()->whereIn('listing_id', $listings->pluck('id'))->avg('rating'),
        ])->title($this->user->name);
    }
}
