<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['key', 'label', 'color', 'base_url', 'handle_type', 'letter', 'logo', 'is_active', 'sort'])]
class Platform extends Model
{
    use RecordsActivity;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort');
    }

    /** آیا شناسه این پلتفرم شماره تلفن است؟ (مثل واتساپ) */
    public function usesPhone(): bool
    {
        return $this->handle_type === 'phone';
    }

    public function profileUrl(string $handle): string
    {
        return 'https://'.$this->base_url.$handle;
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    /**
     * آیدی ورودی کاربر را تمیز می‌کند: لینک کامل، @ و رقم فارسی را می‌پذیرد.
     * برای شماره تلفن، 0912... را به 98912... تبدیل می‌کند.
     */
    public function cleanHandle(string $input): string
    {
        $handle = trim(en_digits($input));
        $handle = preg_replace('#^https?://#', '', $handle);
        $handle = rtrim(strtok($handle, '?'), '/');
        if (str_contains($handle, '/')) {
            $handle = substr($handle, strrpos($handle, '/') + 1);
        }
        $handle = ltrim($handle, '@');

        if ($this->usesPhone()) {
            $handle = preg_replace('/\D/', '', $handle);
            if (str_starts_with($handle, '0')) {
                $handle = '98'.substr($handle, 1);
            }
        }

        return $handle;
    }
}
