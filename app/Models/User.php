<?php

namespace App\Models;

use App\Models\Concerns\HasSlugInUrl;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'mobile', 'password', 'role_id', 'country', 'province', 'city', 'bio', 'avatar', 'verified_at', 'interests'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasSlugInUrl, Notifiable;

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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
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

    // ---------- نقش و دسترسی ----------

    public function hasPermission(string $permission): bool
    {
        return (bool) $this->role?->allows($permission);
    }

    public function isAdmin(): bool
    {
        return $this->role?->key === 'admin';
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
