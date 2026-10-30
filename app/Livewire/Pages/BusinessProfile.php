<?php

namespace App\Livewire\Pages;

use App\Models\Review;
use App\Models\User;
use App\Support\Seo;
use Livewire\Component;
use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * صفحه عمومی یک کسب‌وکار با همه پیج‌های تأییدشده‌اش.
 */
class BusinessProfile extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        Seo::redirectToCanonical($user, 'business.show', 'user');

        $listings = $user->listings()->approved()->get(['id', 'title', 'platform_id', 'handle']);
        $place = $user->placeLabel() ? ' در '.$user->placeLabel() : '';

        Seo::set(new SEOData(
            title: $user->name.' | پیج‌ها و کانال‌ها',
            description: Seo::excerpt($user->bio ?: "همه پیج‌ها و کانال‌های {$user->name}{$place} در نشونی: ".$listings->pluck('title')->implode('، ')),
            image: $user->avatarUrl() ? ltrim($user->avatarUrl(), '/') : null,
            url: route('business.show', $user),
            schema: Seo::schemas([
                '@context' => 'https://schema.org',
                '@type' => 'ProfilePage',
                'mainEntity' => array_filter([
                    '@type' => 'Organization',
                    'name' => $user->name,
                    'description' => $user->bio,
                    'url' => route('business.show', $user),
                    'sameAs' => $listings->map->externalUrl()->values()->all(),
                ]),
            ]),
            type: 'profile',
        ));

        // کسب‌وکاری که هنوز پیج تأییدشده ندارد، صفحه خالی است و نباید ایندکس شود
        if ($listings->isEmpty()) {
            Seo::get()->markAsNoindex();
        }
    }

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
