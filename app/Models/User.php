<?php

namespace App\Models;

use App\Models\Concerns\HasSlugInUrl;
use App\Models\Concerns\RecordsActivity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read Role|null $role
 */
#[Fillable(['name', 'mobile', 'password', 'country', 'province', 'city', 'bio', 'avatar', 'verified_at', 'interests'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasSlugInUrl, Notifiable;

    use RecordsActivity;

    /** از تغییرات حساب فقط موارد امنیتی ثبت می‌شوند (تغییر نقش جداگانه در setRole ثبت می‌شود) */
    protected function activityOnly(): ?array
    {
        return ['name', 'mobile', 'password', 'verified_at'];
    }

    /** نامک آدرس صفحه عمومی کسب‌وکار: /u/2-گالری-ماهور */
    protected function slugSource(): string
    {
        return $this->name;
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'verified_at' => 'datetime',
            'interests' => 'array',
        ];
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /** پیج‌هایی که کاربر دنبال می‌کند */
    public function follows(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class, 'follows')->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }

    // ---------- نقش و دسترسی (spatie/laravel-permission) ----------
    // در این سایت هر کاربر فقط یک نقش دارد؛ نقش با setRole تعیین می‌شود.

    /**
     * تنها نقش کاربر: $user->role
     * (accessor قدیمی get...Attribute عمداً؛ متد role() با scope «User::role('admin')» پکیج تداخل دارد)
     */
    public function getRoleAttribute(): ?Role
    {
        /** @var Role|null */
        return $this->roles->first();
    }

    /** جایگزینی نقش فعلی با نقش داده‌شده و ثبت آن در تاریخچه اقدامات */
    public function setRole(Role|string $role): static
    {
        $old = $this->role?->label;
        $this->syncRoles([$role]);
        $new = $this->unsetRelation('roles')->role?->label;

        if ($this->wasRecentlyCreated || $old === $new) {
            return $this;
        }

        $this->logActivity('updated', ['role' => ['old' => $old, 'new' => $new]]);

        return $this;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->isAdmin() || $this->checkPermissionTo($permission);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /** مدیر، ناظر یا هر نقشی که به پنل دسترسی دارد */
    public function isStaff(): bool
    {
        return (bool) $this->role?->hasPanelAccess();
    }

    public function roleLabel(): string
    {
        return $this->role?->label ?? 'کاربر';
    }

    // ---------- نمایش ----------

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }

    public function initial(): string
    {
        return Str::substr(trim($this->name), 0, 1);
    }

    /** «کاشان، اصفهان» (اگر نام شهر و استان یکی باشد فقط یک‌بار) */
    public function placeLabel(): string
    {
        if (! $this->city) {
            return '';
        }

        return $this->province && $this->province !== $this->city ? "{$this->city}، {$this->province}" : $this->city;
    }
}
