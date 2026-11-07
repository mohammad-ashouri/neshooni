<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ساخت حساب مدیر اصلی سایت.
 * شماره و رمز از .env خوانده می‌شود (ADMIN_MOBILE و ADMIN_PASSWORD) تا رمز وارد گیت نشود.
 * اگر یکی از این دو خالی باشد، چیزی ساخته نمی‌شود.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $mobile = env('ADMIN_MOBILE');
        $password = env('ADMIN_PASSWORD');

        if (blank($mobile) || blank($password)) {
            $this->command?->warn('ADMIN_MOBILE یا ADMIN_PASSWORD در .env تنظیم نشده؛ حساب مدیر ساخته نشد.');

            return;
        }

        User::updateOrCreate(
            ['mobile' => $mobile],
            [
                'name' => env('ADMIN_NAME', 'مدیر'),
                'password' => $password,
                'role_id' => Role::where('key', 'admin')->value('id'),
            ],
        );
    }
}
