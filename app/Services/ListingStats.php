<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingDailyStat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * ثبت و خواندن آمار بازدید و کلیک پیج‌ها.
 */
class ListingStats
{
    /**
     * یک بازدید یا کلیک ثبت می‌کند. هر نفر در هر نشست فقط یک‌بار شمرده می‌شود.
     *
     * @param  'views'|'clicks'  $field
     */
    public function record(Listing $listing, string $field): void
    {
        $sessionKey = "counted.{$field}.{$listing->id}";
        if (session()->has($sessionKey)) {
            return;
        }
        session()->put($sessionKey, true);

        $listing->increment($field);

        $row = ListingDailyStat::firstOrCreate(['listing_id' => $listing->id, 'date' => today()->toDateString()]);
        $row->increment($field);
    }

    /**
     * سری روزانه برای نمودار: ['labels' => [Carbon...], 'views' => [...], 'clicks' => [...]]
     *
     * @param  int  $offset  تعداد دوره قبل (۱ = دوره قبلی، برای مقایسه)
     */
    public function series(Collection|array $listingIds, int $days, int $offset = 0): array
    {
        $end = today()->subDays($offset * $days);
        $start = $end->copy()->subDays($days - 1);

        $rows = ListingDailyStat::query()
            ->whereIn('listing_id', $listingIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('date, SUM(views) as views, SUM(clicks) as clicks')
            ->groupBy('date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString());

        $labels = $views = $clicks = [];
        for ($date = $start->copy(); $date->lte($end); $date = $date->addDay()) {
            $row = $rows->get($date->toDateString());
            $labels[] = $date;
            $views[] = (int) ($row->views ?? 0);
            $clicks[] = (int) ($row->clicks ?? 0);
        }

        return compact('labels', 'views', 'clicks');
    }

    /** درصد تغییر نسبت به دوره قبل (null اگر دوره قبل صفر بوده) */
    public static function change(int $current, int $previous): ?int
    {
        return $previous ? (int) round(($current - $previous) / $previous * 100) : null;
    }
}
