<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * یک ردیف از تاریخچه اقدامات (ثبت خودکار با RecordsActivity).
 * فقط اضافه می‌شود و هیچ‌جای سایت ویرایش یا حذفش نمی‌کند.
 */
#[Fillable(['user_id', 'event', 'subject_type', 'subject_id', 'subject_label', 'changes', 'ip'])]
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    /** نوع مورد → [کلاس، برچسب فارسی] (کلید همان چیزی است که در فیلتر آدرس می‌آید) */
    public const SUBJECTS = [
        'listing' => [Listing::class, 'پیج'],
        'photo' => [ListingPhoto::class, 'تصویر'],
        'review' => [Review::class, 'نظر'],
        'report' => [Report::class, 'گزارش تخلف'],
        'verification' => [VerificationRequest::class, 'درخواست نشان تأیید'],
        'payment' => [Payment::class, 'پرداخت'],
        'user' => [User::class, 'کاربر'],
        'role' => [Role::class, 'نقش'],
        'category' => [Category::class, 'دسته'],
        'platform' => [Platform::class, 'پلتفرم'],
        'message' => [ContactMessage::class, 'پیام تماس'],
        'setting' => [Setting::class, 'تنظیمات'],
    ];

    public const EVENTS = [
        'created' => 'ایجاد',
        'updated' => 'ویرایش',
        'deleted' => 'حذف',
    ];

    /** برچسب فارسی فیلدهای پرتکرار */
    public const FIELDS = [
        'status' => 'وضعیت',
        'reject_reason' => 'دلیل رد',
        'reason' => 'دلیل',
        'title' => 'عنوان',
        'name' => 'نام',
        'label' => 'برچسب',
        'description' => 'توضیحات',
        'handle' => 'آیدی',
        'image' => 'تصویر',
        'phone' => 'تلفن',
        'address' => 'آدرس',
        'province' => 'استان',
        'city' => 'شهر',
        'hours' => 'ساعات کاری',
        'category_id' => 'دسته',
        'platform_id' => 'پلتفرم',
        'featured_until' => 'ویژه تا',
        'published_at' => 'تاریخ انتشار',
        'role_id' => 'نقش',
        'role' => 'نقش',
        'verified_at' => 'نشان تأیید',
        'mobile' => 'موبایل',
        'password' => 'رمز عبور',
        'permissions' => 'دسترسی‌ها',
        'rating' => 'امتیاز',
        'body' => 'متن',
        'reply' => 'پاسخ',
        'note' => 'یادداشت',
        'value' => 'مقدار',
        'is_active' => 'فعال',
    ];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function subjectKey(string $class): ?string
    {
        foreach (self::SUBJECTS as $key => [$subjectClass]) {
            if ($subjectClass === $class) {
                return $key;
            }
        }

        return null;
    }

    public function subjectName(): string
    {
        $key = self::subjectKey($this->subject_type);

        return $key ? self::SUBJECTS[$key][1] : class_basename($this->subject_type);
    }

    public function eventName(): string
    {
        return self::EVENTS[$this->event] ?? $this->event;
    }

    public static function fieldName(string $field): string
    {
        return self::FIELDS[$field] ?? $field;
    }
}
