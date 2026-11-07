<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'label', 'description', 'permissions', 'is_system'])]
class Role extends Model
{
    use RecordsActivity;

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function allows(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    /** آیا این نقش به پنل مدیریت راه دارد؟ (حداقل یک دسترسی) */
    public function hasPanelAccess(): bool
    {
        return ! empty($this->permissions);
    }

    /** برچسب فارسی دسترسی‌ها برای نمایش */
    public function permissionLabels(): array
    {
        $labels = collect(config('permissions'))->collapse();

        return collect($this->permissions)
            ->map(fn (string $key) => $key === '*' ? 'همه دسترسی‌ها' : $labels[$key] ?? $key)
            ->all();
    }
}
