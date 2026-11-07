<div>
    <h1 class="text-2xl font-extrabold">تاریخچه اقدامات</h1>
    <p class="mt-1 mb-5 text-sm text-slate-500">هر تأیید، رد، ویرایش و حذف با نام انجام‌دهنده و زمان ثبت می‌شود. این فهرست قابل ویرایش یا حذف نیست.</p>

    <div class="card mb-4 flex flex-wrap items-center gap-3 p-4">
        <select wire:model.live="subject" class="input w-auto py-2">
            <option value="">همه موارد</option>
            @foreach (App\Models\ActivityLog::SUBJECTS as $key => [, $label])
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        @if ($subject)
            <input wire:model.live.debounce.400ms="subjectId" class="input w-28 py-2" dir="ltr" placeholder="شناسه">
        @endif
        <input wire:model.live.debounce.400ms="q" class="input w-full py-2 sm:w-56" placeholder="🔍 نام یا موبایل انجام‌دهنده">
        <label class="filter-chip flex cursor-pointer items-center gap-1.5">
            <input type="checkbox" wire:model.live="staff" class="accent-violet-600"> فقط مدیران و ناظران
        </label>
    </div>

    @if ($logs->isEmpty())
        <x-empty icon="🗂️" title="موردی ثبت نشده" />
    @else
        <div class="card divide-y divide-slate-100">
            @foreach ($logs as $log)
                <div class="px-5 py-3.5 text-sm" wire:key="log-{{ $log->id }}">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span @class([
                            'chip',
                            'bg-emerald-100 text-emerald-700' => $log->event === 'created',
                            'bg-sky-100 text-sky-700' => $log->event === 'updated',
                            'bg-rose-100 text-rose-700' => $log->event === 'deleted',
                        ])>{{ $log->eventName() }}</span>
                        <span class="text-slate-500">{{ $log->subjectName() }}</span>
                        <span class="font-semibold">{{ $log->subject_label }}</span>
                        <span class="text-xs text-slate-400" dir="ltr">#{{ fa_digits($log->subject_id) }}</span>
                        <span class="ms-auto text-xs text-slate-400">{{ fa_datetime($log->created_at) }}</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        توسط
                        @if ($log->user)
                            <span class="font-semibold text-slate-700">{{ $log->user->name }}</span>
                            @if ($log->user->isStaff()) <span class="chip bg-violet-100 text-violet-700">{{ $log->user->roleLabel() }}</span> @endif
                        @else
                            <span class="font-semibold text-slate-700">سیستم یا مهمان</span>
                        @endif
                        @if ($log->ip) · <span dir="ltr">{{ $log->ip }}</span> @endif
                    </div>

                    @if ($log->changes)
                        <dl class="mt-2 grid gap-1 rounded-xl bg-slate-50 p-3 text-xs">
                            @foreach ($log->changes as $field => $change)
                                <div class="flex flex-wrap gap-1.5">
                                    <dt class="font-semibold text-slate-600">{{ App\Models\ActivityLog::fieldName($field) }}:</dt>
                                    <dd class="min-w-0 break-words">
                                        <span class="text-rose-600 line-through">{{ is_scalar($change['old'] ?? null) ? Str::limit((string) $change['old'], 120) : json_encode($change['old'] ?? null, JSON_UNESCAPED_UNICODE) }}</span>
                                        ←
                                        <span class="text-emerald-700">{{ is_scalar($change['new'] ?? null) ? Str::limit((string) $change['new'], 120) : json_encode($change['new'] ?? null, JSON_UNESCAPED_UNICODE) }}</span>
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    @endif
</div>
