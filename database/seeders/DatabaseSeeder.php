<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * داده‌های پایه (نقش‌ها، پلتفرم‌ها، دسته‌ها) همیشه لازم‌اند.
     * DemoSeeder (داده فیک) به‌طور پیش‌فرض غیرفعال است؛ فقط با SEED_DEMO=true در .env اجرا می‌شود.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PlatformSeeder::class,
            CategorySeeder::class,
        ]);

        if (! app()->isProduction() && env('SEED_DEMO', false)) {
            $this->call(DemoSeeder::class);
        }
    }
}
