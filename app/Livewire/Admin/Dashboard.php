<?php

namespace App\Livewire\Admin;

use App\Enums\ModerationStatus;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use App\Services\ListingStats;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::admin'), Title('داشبورد مدیریت')]
class Dashboard extends Component
{
    #[Url]
    public int $days = 30;

    public function mount()
    {
        // نقشی که آمار نمی‌بیند به اولین بخشی که دسترسی دارد می‌رود
        if (Gate::denies('stats.view')) {
            $routes = [
                'listings.moderate' => 'admin.listings', 'images.moderate' => 'admin.photos', 'reviews.moderate' => 'admin.reviews',
                'reports.handle' => 'admin.reports', 'messages.read' => 'admin.messages', 'newsletter.view' => 'admin.newsletter',
                'verifications.manage' => 'admin.verifications', 'payments.view' => 'admin.payments', 'categories.manage' => 'admin.categories',
                'platforms.manage' => 'admin.platforms', 'users.manage' => 'admin.users',
            ];
            $permission = collect($routes)->keys()->first(fn ($permission) => Gate::allows($permission));

            return $this->redirectRoute($routes[$permission] ?? 'admin.help', navigate: true);
        }
    }

    public function render(ListingStats $stats)
    {
        abort_unless(in_array($this->days, [7, 30, 90]), 404);

        $ids = Listing::pluck('id');
        $current = $stats->series($ids, $this->days);
        $previous = $stats->series($ids, $this->days, 1);
        $since = now()->subDays($this->days);

        return view('livewire.admin.dashboard', [
            'series' => $current,
            'tiles' => [
                ['👁️ بازدید', array_sum($current['views']), ListingStats::change(array_sum($current['views']), array_sum($previous['views']))],
                ['🖱️ کلیک', array_sum($current['clicks']), ListingStats::change(array_sum($current['clicks']), array_sum($previous['clicks']))],
                ['👥 کاربر جدید', User::where('created_at', '>=', $since)->count(), null],
                ['📋 پیج جدید', Listing::where('created_at', '>=', $since)->count(), null],
            ],
            'revenue' => Gate::allows('payments.view') ? Payment::paid()->where('paid_at', '>=', $since)->sum('amount') : null,
            'pending' => [
                ['پیج در انتظار', Listing::where('status', ModerationStatus::Pending)->count(), 'admin.listings', 'listings.moderate'],
                ['نظر در انتظار', Review::where('status', ModerationStatus::Pending)->count(), 'admin.reviews', 'reviews.moderate'],
                ['گزارش باز', Report::where('status', 'open')->count(), 'admin.reports', 'reports.handle'],
            ],
            'totals' => [
                'listings' => Listing::approved()->count(),
                'users' => User::count(),
                'reviews' => Review::approved()->count(),
            ],
            'top' => Listing::approved()->with('platform')->orderByDesc('views')->limit(5)->get(),
        ]);
    }
}
