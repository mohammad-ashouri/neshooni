<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * گزارش تخلف؛ reportable می‌تواند Listing یا Review باشد.
 */
#[Fillable(['reporter_id', 'reportable_type', 'reportable_id', 'reason', 'body', 'status', 'note'])]
class Report extends Model
{
    use RecordsActivity;

    public function activityLabel(): string
    {
        return 'گزارش «'.$this->reason.'»';
    }

    /** دلیل‌های قابل انتخاب برای هر نوع */
    public const REASONS = [
        'listing' => ['کلاهبرداری یا فروش جعلی', 'محتوای غیراخلاقی یا نامناسب', 'اطلاعات یا لینک نادرست', 'پیج تکراری', 'نقض حقوق دیگران', 'سایر'],
        'review' => ['توهین یا الفاظ نامناسب', 'اسپم یا تبلیغات', 'اطلاعات نادرست', 'سایر'],
    ];

    protected function casts(): array
    {
        return ['status' => ReportStatus::class];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isAboutListing(): bool
    {
        return $this->reportable_type === Listing::class;
    }
}
