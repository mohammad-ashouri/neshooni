<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'key' => 'admin',
            'label' => 'مدیر',
            'description' => 'دسترسی کامل به همه بخش‌ها',
            'permissions' => ['*'],
            'is_system' => true,
        ]);

        Role::create([
            'key' => 'moderator',
            'label' => 'ناظر',
            'description' => 'بررسی محتوا بدون دسترسی به حذف و تنظیمات',
            'permissions' => ['stats.view', 'listings.moderate', 'images.moderate', 'reviews.moderate', 'reports.handle', 'messages.read'],
        ]);

        Role::create([
            'key' => 'user',
            'label' => 'کاربر',
            'description' => 'کاربر عادی بدون دسترسی به پنل',
            'permissions' => [],
            'is_system' => true,
        ]);
    }
}
