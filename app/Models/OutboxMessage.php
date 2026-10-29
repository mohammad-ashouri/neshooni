<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * صف پیام‌های پیامکی/ایمیلی برای مشترکان مهمان.
 * ارسال واقعی باید با یک Job و سرویس پیامک انجام شود (در مستندات توضیح داده شده).
 */
#[Fillable(['contact', 'body', 'sent_at'])]
class OutboxMessage extends Model
{
    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }
}
