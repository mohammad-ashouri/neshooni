<div>
    <h1 class="mb-5 text-2xl font-extrabold">گزارش‌های تخلف</h1>

    <div class="mb-4 flex gap-2">
        <button wire:click="$set('status', 'open')" @class(['cursor-pointer filter-chip', 'filter-chip-active' => $status === 'open'])>باز ({{ fa_number($openCount) }})</button>
        <button wire:click="$set('status', 'closed')" @class(['cursor-pointer filter-chip', 'filter-chip-active' => $status !== 'open'])>بسته‌شده</button>
    </div>

    @if ($reports->isEmpty())
        <x-empty icon="🚩" title="گزارشی نیست" />
    @else
        <div class="space-y-3">
            @foreach ($reports as $report)
                @php($target = $report->reportable)
                <article class="card p-5" wire:key="report-{{ $report->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="text-sm">
                            <span class="chip bg-slate-100 text-slate-600">{{ $report->isAboutListing() ? '📋 پیج' : '💬 نظر' }}</span>
                            <span class="ms-1 font-bold text-rose-700">{{ $report->reason }}</span>
                            <div class="mt-1 text-xs text-slate-400">گزارش‌دهنده: {{ $report->reporter->name }} · {{ fa_datetime($report->created_at) }}</div>
                        </div>
                        <span class="chip ring-1 ring-inset {{ $report->status->badgeClass() }}">{{ $report->status->label() }}</span>
                    </div>

                    @if ($report->body)
                        <p class="mt-3 text-sm leading-7">{{ $report->body }}</p>
                    @endif

                    {{-- مورد گزارش‌شده --}}
                    <div class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm">
                        @if (! $target)
                            <span class="text-slate-400">این مورد حذف شده است.</span>
                        @elseif ($report->isAboutListing())
                            <a href="{{ route('listings.show', $target) }}" wire:navigate class="cursor-pointer font-semibold text-violet-700 hover:underline">{{ $target->title }}</a>
                            <x-status-badge :status="$target->status" class="ms-1" />
                        @else
                            <div class="text-xs text-slate-500">نظر {{ $target->user->name }} درباره <a href="{{ route('listings.show', $target->listing_id) }}" wire:navigate class="cursor-pointer text-violet-700 hover:underline">{{ $target->listing->title }}</a> <x-status-badge :status="$target->status" class="ms-1" /></div>
                            <p class="mt-1">{{ $target->body }}</p>
                        @endif
                    </div>

                    @if ($report->status === App\Enums\ReportStatus::Open)
                        <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                            <input wire:model="notes.{{ $report->id }}" class="input flex-1 py-2 text-sm" placeholder="یادداشت مدیر (اختیاری)">
                            @if ($target)
                                <button wire:click="disableTarget({{ $report->id }})" wire:confirm="مورد گزارش‌شده غیرفعال شود؟" class="cursor-pointer btn-danger py-2">غیرفعال کردن</button>
                            @endif
                            <button wire:click="close({{ $report->id }}, 'resolved')" class="cursor-pointer btn-light py-2">بستن</button>
                            <button wire:click="close({{ $report->id }}, 'dismissed')" class="cursor-pointer btn-light py-2">گزارش بی‌مورد</button>
                        </div>
                    @elseif ($report->note)
                        <p class="mt-2 text-xs text-slate-500">یادداشت: {{ $report->note }}</p>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    @endif
</div>
