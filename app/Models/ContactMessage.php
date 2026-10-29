<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'contact', 'subject', 'body', 'read_at'])]
class ContactMessage extends Model
{
    public const SUBJECTS = ['پشتیبانی', 'گزارش تخلف', 'همکاری', 'پیشنهاد و انتقاد', 'سایر'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isEmail(): bool
    {
        return str_contains($this->contact, '@');
    }
}
