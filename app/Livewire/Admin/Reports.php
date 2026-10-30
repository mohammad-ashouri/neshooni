<?php

namespace App\Livewire\Admin;

use App\Enums\ModerationStatus;
use App\Enums\ReportStatus;
use App\Models\Listing;
use App\Models\Report;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * رسیدگی به گزارش‌های تخلف پیج‌ها و نظرات.
 * «غیرفعال کردن» یعنی پیج یا نظر گزارش‌شده رد می‌شود و گزارش بسته می‌شود.
 */
#[Layout('layouts::admin'), Title('گزارش‌های تخلف')]
class Reports extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'open';

    /** یادداشت مدیر برای هر گزارش: [report_id => 'متن'] */
    public array $notes = [];

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function disableTarget(int $id): void
    {
        $report = Report::with('reportable')->findOrFail($id);
        $target = $report->reportable;

        if ($target instanceof Listing) {
            $target->update(['status' => ModerationStatus::Rejected, 'reject_reason' => 'غیرفعال به دلیل گزارش تخلف: '.$report->reason]);
        } elseif ($target) {
            $target->update(['status' => ModerationStatus::Rejected]);
        }

        // همه گزارش‌های باز همین مورد با هم بسته می‌شوند
        Report::where('reportable_type', $report->reportable_type)->where('reportable_id', $report->reportable_id)
            ->where('status', ReportStatus::Open)
            ->update(['status' => ReportStatus::Resolved, 'note' => $this->notes[$id] ?? 'مورد گزارش‌شده غیرفعال شد.']);

        $this->dispatch('toast', message: 'مورد گزارش‌شده غیرفعال شد.');
    }

    public function close(int $id, string $status): void
    {
        Report::findOrFail($id)->update([
            'status' => ReportStatus::from($status),
            'note' => $this->notes[$id] ?? null,
        ]);

        $this->dispatch('toast', message: $status === 'dismissed' ? 'گزارش رد شد.' : 'گزارش بسته شد.');
    }

    public function render()
    {
        return view('livewire.admin.reports', [
            'reports' => Report::with(['reporter', 'reportable'])
                ->when($this->status === 'open', fn ($q) => $q->where('status', ReportStatus::Open), fn ($q) => $q->where('status', '!=', ReportStatus::Open))
                ->latest()
                ->paginate(15),
            'openCount' => Report::where('status', ReportStatus::Open)->count(),
        ]);
    }
}
