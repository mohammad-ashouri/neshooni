<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\OutboxMessage;
use App\Models\Subscription;
use App\Notifications\SiteMessage;

/**
 * وقتی پیجی برای اولین بار منتشر می‌شود، مشترکان دسته آن (و دسته‌های بالاتر) را باخبر می‌کند.
 * کاربر عضو: اعلان داخل سایت | مهمان: یک پیام در صف outbox برای پیامک/ایمیل.
 */
class SubscriptionNotifier
{
    /** @return int تعداد افرادی که باخبر شدند */
    public function notify(Listing $listing): int
    {
        $categoryIds = $listing->category->path()->pluck('id');
        $alreadyNotified = [];

        $subscriptions = Subscription::with(['user', 'category'])
            ->whereIn('category_id', $categoryIds)
            ->get();

        foreach ($subscriptions as $subscription) {
            // هر نفر فقط یک‌بار، و صاحب خود پیج باخبر نمی‌شود
            $who = $subscription->user_id ?: $subscription->contact;
            if (in_array($who, $alreadyNotified, true) || $subscription->user_id === $listing->user_id) {
                continue;
            }
            $alreadyNotified[] = $who;

            $text = "پیج جدید در «{$subscription->category->name}»: {$listing->title}";

            if ($subscription->user) {
                $subscription->user->notify(new SiteMessage($text, route('listings.show', $listing)));
            } else {
                OutboxMessage::create(['contact' => $subscription->contact, 'body' => $text.' — '.$listing->publicUrl()]);
            }
        }

        return count($alreadyNotified);
    }
}
