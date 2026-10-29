<?php

namespace App\Models;

use App\Enums\ModerationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['user_id', 'business_type', 'document', 'note', 'status', 'reason'])]
class VerificationRequest extends Model
{
    protected function casts(): array
    {
        return ['status' => ModerationStatus::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documentUrl(): string
    {
        return Storage::url($this->document);
    }

    public function businessTypeLabel(): string
    {
        return $this->business_type === 'company' ? 'شرکت (حقوقی)' : 'شخص حقیقی';
    }
}
