<?php

namespace App\Livewire\Dashboard;

use App\Models\Review;
use App\Services\ListingStats;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * آمار بازدید و کلیک پیج‌های کاربر در بازه ۷، ۳۰ یا ۹۰ روزه
 * با مقایسه نسبت به بازه قبلی.
 */
#[Title('آمار بازدید')]
class Stats extends Component
{
    #[Url]
    public int $days = 30;

    /** شناسه یک پیج یا 0 برای همه */
    #[Url]
    public int $listingId = 0;

    public function render(ListingStats $stats)
    {
        abort_unless(in_array($this->days, [7, 30, 90]), 404);

        $listings = Auth::user()->listings()->withCount('followers')->get();
        $ids = $this->listingId ? $listings->where('id', $this->listingId)->pluck('id') : $listings->pluck('id');

        $current = $stats->series($ids, $this->days);
        $previous = $stats->series($ids, $this->days, 1);

        $tile = fn (string $key) => [
            'value' => array_sum($current[$key]),
            'change' => ListingStats::change(array_sum($current[$key]), array_sum($previous[$key])),
        ];

        // بازدید هر پیج در همین بازه برای نمودار میله‌ای
        $perListing = DB::table('listing_daily_stats')
            ->whereIn('listing_id', $listings->pluck('id'))
            ->where('date', '>=', today()->subDays($this->days - 1)->toDateString())
            ->groupBy('listing_id')
            ->selectRaw('listing_id, SUM(views) as views')
            ->pluck('views', 'listing_id');

        $reviews = Review::approved()->whereIn('listing_id', $ids);

        return view('livewire.dashboard.stats', [
            'listings' => $listings,
            'series' => $current,
            'views' => $tile('views'),
            'clicks' => $tile('clicks'),
            'followers' => $listings->whereIn('id', $ids)->sum('followers_count'),
            'rating' => ['average' => (clone $reviews)->avg('rating'), 'count' => (clone $reviews)->count()],
            'bars' => $listings->map(fn ($l) => ['label' => $l->title, 'value' => (int) ($perListing[$l->id] ?? 0)])->sortByDesc('value')->values()->all(),
        ]);
    }
}
