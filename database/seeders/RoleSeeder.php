<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * دسترسی‌های config/permissions.php و نقش‌های پایه (spatie/laravel-permission).
 * چندبار اجرا شدنش بی‌خطر است: دسترسی‌های تازه config اضافه می‌شوند و نقش‌های موجود دست نمی‌خورند.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (collect(config('permissions'))->collapse()->keys() as $name) {
            Permission::findOrCreate($name);
        }

        // مدیر کل دسترسی ثبت‌شده ندارد؛ همه را از Gate::before می‌گیرد
        $this->role(Role::ADMIN, 'مدیر', 'دسترسی کامل به همه بخش‌ها', system: true);

        $this->role('moderator', 'ناظر', 'بررسی محتوا بدون دسترسی به حذف و تنظیمات',
            permissions: ['stats.view', 'listings.moderate', 'images.moderate', 'reviews.moderate', 'reports.handle', 'messages.read']);

        $this->role(Role::USER, 'کاربر', 'کاربر عادی بدون دسترسی به پنل', system: true);
    }

    private function role(string $name, string $label, string $description, array $permissions = [], bool $system = false): void
    {
        $role = Role::firstOrNew(['name' => $name, 'guard_name' => 'web']);

        if ($role->exists) {
            return;
        }

        $role->fill(['label' => $label, 'description' => $description, 'is_system' => $system])->save();
        $role->syncPermissions($permissions);
    }
}
