<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\OutboxMessage;
use App\Models\Subscription;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * آمار مشترکان خبرنامه و صف پیام‌های ارسالی به مهمان‌ها.
 * TODO: یک دستور زمان‌بندی‌شده (schedule) پیام‌های outbox را با پنل پیامک/ایمیل ارسال و sent_at را پر کند.
 */
#[Layout('layouts::admin'), Title('خبرنامه')]
class Newsletter extends Component
{
    public function render()
    {
        $perCategory = Subscription::selectRaw('category_id, count(*) as total')->groupBy('category_id')->pluck('total', 'category_id');

        return view('livewire.admin.newsletter', [
            'total' => Subscription::count(),
            'members' => Subscription::whereNotNull('user_id')->distinct()->count('user_id'),
            'guests' => Subscription::whereNull('user_id')->distinct()->count('contact'),
            'general' => $perCategory[''] ?? 0,
            'categories' => Category::flatTree()
                ->map(fn ($row) => $row + ['count' => $perCategory[$row['category']->id] ?? 0])
                ->where('count', '>', 0)
                ->sortByDesc('count')
                ->values(),
            'outbox' => OutboxMessage::latest()->limit(30)->get(),
        ]);
    }
}
