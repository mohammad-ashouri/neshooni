<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Support\Str;

/**
 * ثبت خودکار ایجاد، ویرایش و حذف مدل در تاریخچه اقدامات (activity_logs).
 * هم کار مدیران و ناظران ثبت می‌شود و هم تغییراتی که صاحب پیج در اطلاعاتش می‌دهد.
 *
 * نکته: به‌روزرسانی گروهی با کوئری (Model::whereIn(...)->update()) رویداد مدل ندارد و ثبت نمی‌شود؛
 * برای کارهای مدیریتی روی تک‌تک مدل‌ها update/delete صدا زده شود.
 */
trait RecordsActivity
{
    public static function bootRecordsActivity(): void
    {
        foreach (static::activityEvents() as $event) {
            static::{$event}(fn (self $model) => $model->recordActivity($event));
        }
    }

    /** کدام رویدادها ثبت شوند */
    protected static function activityEvents(): array
    {
        return ['created', 'updated', 'deleted'];
    }

    /** فیلدهایی که تغییرشان ثبت نمی‌شود (مثل شمارنده بازدید) */
    protected function activityIgnored(): array
    {
        return [];
    }

    /** اگر آرایه باشد فقط همین فیلدها ثبت می‌شوند */
    protected function activityOnly(): ?array
    {
        return null;
    }

    /** نامی که در تاریخچه نشان داده می‌شود */
    public function activityLabel(): string
    {
        return (string) ($this->title ?? $this->name ?? $this->label ?? $this->subject ?? '#'.$this->getKey());
    }

    public function recordActivity(string $event): void
    {
        $changes = null;

        if ($event === 'updated') {
            $changes = $this->activityChanges();
            if (! $changes) {
                return;
            }
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'subject_type' => static::class,
            'subject_id' => (string) $this->getKey(),
            'subject_label' => Str::limit($this->activityLabel(), 250),
            'changes' => $changes,
            'ip' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }

    /** @return array<string, array{old: mixed, new: mixed}> */
    private function activityChanges(): array
    {
        $ignored = [...$this->activityIgnored(), 'created_at', 'updated_at', 'remember_token'];
        $only = $this->activityOnly();
        $changes = [];

        foreach ($this->getChanges() as $field => $new) {
            if (in_array($field, $ignored, true) || ($only !== null && ! in_array($field, $only, true))) {
                continue;
            }

            // مقدار فیلدهای مخفی (مثل رمز عبور) ثبت نمی‌شود؛ فقط اینکه عوض شده
            $secret = in_array($field, $this->getHidden(), true);
            $changes[$field] = [
                'old' => $secret ? '***' : $this->activityValue($this->getRawOriginal($field)),
                'new' => $secret ? '***' : $this->activityValue($new),
            ];
        }

        return $changes;
    }

    private function activityValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return is_string($value) ? Str::limit($value, 500) : $value;
    }
}
