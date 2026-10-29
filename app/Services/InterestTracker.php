<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

/**
 * ردیابی علاقه‌مندی کاربر به دسته‌بندی‌ها برای پیشنهاد خبرنامه.
 *
 * هر بار که کاربر پیجی را می‌بیند، صفحه دسته‌ای را باز می‌کند، نظر می‌دهد یا دنبال می‌کند،
 * به دسته آن پیج و همه دسته‌های بالاترش امتیاز داده می‌شود (config/site.php → interest_points).
 * وقتی امتیاز دسته‌ای به حد نصاب برسد، مودال خبرنامه برای همان دسته نشان داده می‌شود.
 *
 * کاربر عضو: داده در ستون users.interests | مهمان: داده در session
 * ساختار داده: ['scores' => [category_id => points], 'dismissed' => [category_id => timestamp], 'general_dismissed' => timestamp]
 */
class InterestTracker
{
    public function add(?int $categoryId, string $action): void
    {
        $category = Category::fromCache($categoryId);
        if (! $category) {
            return;
        }

        $data = $this->data();
        $points = config("site.interest_points.{$action}", 1);

        foreach ($category->path() as $node) {
            $data['scores'][$node->id] = ($data['scores'][$node->id] ?? 0) + $points;
        }

        $this->save($data);
    }

    /** دسته پیشنهادی برای مودال؛ بین دسته‌های هم‌امتیاز، دقیق‌ترین (عمیق‌ترین) انتخاب می‌شود */
    public function suggestion(): ?Category
    {
        $data = $this->data();
        $cooldown = now()->subDays(config('site.interest_cooldown_days'))->timestamp;

        return collect($data['scores'])
            ->filter(fn (int $score, int $id) => $score >= config('site.interest_threshold')
                && ($data['dismissed'][$id] ?? 0) < $cooldown
                && ! $this->isSubscribed($id))
            ->map(fn (int $score, int $id) => ['category' => Category::fromCache($id), 'score' => $score])
            ->filter(fn (array $item) => $item['category'] !== null)
            ->sortBy([['score', 'desc'], [fn (array $a, array $b) => $b['category']->depth() <=> $a['category']->depth()]])
            ->first()['category'] ?? null;
    }

    /** آیا خبرنامه عمومی را به این مهمان نشان بدهیم؟ (بعد از چند صفحه، اگر علاقه مشخصی ندارد) */
    public function shouldOfferGeneralNewsletter(): bool
    {
        if (Auth::check() || session('newsletter_subscribed')) {
            return false;
        }

        $dismissedAt = $this->data()['general_dismissed'] ?? 0;

        return session('guest_pages', 0) >= config('site.guest_newsletter_after_pages')
            && $dismissedAt < now()->subDays(config('site.interest_cooldown_days'))->timestamp;
    }

    public function dismiss(?int $categoryId): void
    {
        $data = $this->data();
        if ($categoryId) {
            $data['dismissed'][$categoryId] = now()->timestamp;
        } else {
            $data['general_dismissed'] = now()->timestamp;
        }
        $this->save($data);
    }

    /** پرامتیازترین علاقه‌مندی‌ها: [['category' => Category, 'score' => int], ...] */
    public function top(int $limit = 6): array
    {
        return collect($this->data()['scores'])
            ->sortDesc()
            ->take($limit)
            ->map(fn (int $score, int $id) => ['category' => Category::fromCache($id), 'score' => $score])
            ->filter(fn (array $item) => $item['category'] !== null)
            ->values()
            ->all();
    }

    private function isSubscribed(int $categoryId): bool
    {
        if (Auth::check()) {
            return Subscription::where('user_id', Auth::id())->where('category_id', $categoryId)->exists();
        }

        return in_array($categoryId, session('subscribed_categories', []));
    }

    private function data(): array
    {
        $data = Auth::check() ? (Auth::user()->interests ?? []) : session('interests', []);

        return $data + ['scores' => [], 'dismissed' => []];
    }

    private function save(array $data): void
    {
        if (Auth::check()) {
            Auth::user()->forceFill(['interests' => $data])->save();
        } else {
            session(['interests' => $data]);
        }
    }
}
