<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * اشتراک خبرنامه. اگر category_id خالی باشد یعنی خبرنامه عمومی.
 * کاربر عضو با user_id و مهمان با contact (موبایل یا ایمیل) ثبت می‌شود.
 */
#[Fillable(['user_id', 'contact', 'category_id'])]
class Subscription extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
