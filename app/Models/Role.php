<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * نقش (spatie/laravel-permission) با برچسب فارسی، توضیح و نشان «سیستمی».
 * کلید انگلیسی نقش در ستون name است (admin، moderator، user، ...).
 * نقش admin هیچ دسترسی ثبت‌شده‌ای ندارد و همه دسترسی‌ها را از Gate::before می‌گیرد.
 */
#[Fillable(['name', 'guard_name', 'label', 'description', 'is_system'])]
class Role extends SpatieRole
{
    use RecordsActivity;

    public const ADMIN = 'admin';

    public const USER = 'user';

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function activityLabel(): string
    {
        return $this->label ?: $this->name;
    }

    public function isSuperAdmin(): bool
    {
        return $this->name === self::ADMIN;
    }

    /** @return list<string> کلید دسترسی‌های این نقش */
    public function permissionNames(): array
    {
        return $this->permissions->pluck('name')->all();
    }

    /** آیا این نقش به پنل مدیریت راه دارد؟ (مدیر کل یا حداقل یک دسترسی) */
    public function hasPanelAccess(): bool
    {
        return $this->isSuperAdmin() || $this->permissions->isNotEmpty();
    }

    /** برچسب فارسی دسترسی‌ها برای نمایش */
    public function permissionLabels(): array
    {
        if ($this->isSuperAdmin()) {
            return ['همه دسترسی‌ها'];
        }

        $labels = collect(config('permissions'))->collapse();

        return collect($this->permissionNames())->map(fn (string $key) => $labels[$key] ?? $key)->all();
    }
}
